<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Retail;
use App\Models\Season;
use App\Models\Calculation;
use App\Models\Consumption;
use App\Models\CalculationType;
use App\Models\ChargeSubCategory;
use Illuminate\Support\Facades\DB;
use App\Models\CalculationRatePlan;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class CalculationService
{
    public function calculate(int $customerId, string $startDate, string $endDate)
    {
        DB::beginTransaction(); // Mulai transaksi

        try {
            // Langkah 1: Ambil Retail yang aktif
            $activeRetails = Retail::where('is_active', true)->get();

            if ($activeRetails->isEmpty()) {
                throw new \Exception('No active retail found.');
            }

            // Langkah 2: Ambil data Consumption sesuai input
            $consumptions = Consumption::where('customer_id', $customerId)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->whereBetween('end_time', [$startDate, $endDate])
                ->get();

            $hourlyConsumptions = $this->generateHourlyConsumption($consumptions);
            Log::info('total_usage');
            Log::info($hourlyConsumptions->sum('usage'));

            // Langkah 3: Tentukan musim yang berlaku
            $season = $this->getSeasonForDateRange($startDate, $endDate);

            // Langkah 4: Ambil hari libur
            $holidays = $this->getHolidays($startDate, $endDate);

            // Langkah 5: Insert data ke tabel calculation_rate_plans
            foreach ($activeRetails as $retail) {
                // Ambil rate plans yang aktif dari retail
                $activeRatePlans = $retail->ratePlans->where('is_active', true);

                foreach ($activeRatePlans as $ratePlan) {
                    $ratePlanId = $ratePlan->id;

                    // Insert ke tabel calculation_rate_plans
                    $calculationRatePlan = CalculationRatePlan::firstOrCreate(
                        [
                            'customer_id' => $customerId,
                            'rate_plan_id' => $ratePlanId,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]
                    );

                    // Ambil ChargeSubCategories yang aktif untuk ratePlan ini
                    $chargeSubCategories = $ratePlan->chargeCategories
                        ->where('is_active', true)
                        ->flatMap(function ($chargeCategory) use ($season) {
                            return $chargeCategory->chargeSubCategories->filter(function ($subChargeCategory) use ($season) {
                                return $subChargeCategory->is_active &&
                                    (!$subChargeCategory->season_id || $subChargeCategory->season_id == $season->id);
                            });
                        });

                    foreach ($chargeSubCategories as $chargeSubCategory) {
                        if ($chargeSubCategory->calculation_type_id == CalculationType::FIX_PER_DAY) {
                            // Calculate FIX_PER_DAY
                            $this->calculateFixPerDay($calculationRatePlan , $chargeSubCategory , $startDate , $endDate);
                        }

                        if ($chargeSubCategory->calculation_type_id == CalculationType::FIX_PER_MONTH) {
                            $this->calculateFixPerMonth($calculationRatePlan , $chargeSubCategory , $hourlyConsumptions,  $startDate , $endDate );
                        }

                        if ($chargeSubCategory->calculation_type_id == CalculationType::PERIOD) {
                            $this->calculatePeriod($calculationRatePlan , $chargeSubCategory , $hourlyConsumptions,  $startDate , $endDate );
                        }
                    }
                }
            }

            DB::commit(); // Commit transaksi jika semua operasi berhasil

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika ada kesalahan
            throw $e; // Lempar exception lebih lanjut jika diperlukan
        }
    }

    protected function getSeasonForDateRange(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Cari musim yang berlaku dalam rentang tanggal
        return Season::whereHas('seasonDates', function ($query) use ($start, $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q) use ($start, $end) {
                      $q->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                  });
            });
        })->first();
    }

    protected function getHolidays(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Ambil fixed holidays
        $fixedHolidays = DB::table('fixed_holidays')
            ->get()
            ->map(function ($holiday) use ($start, $end) {
                $date = Carbon::create($start->year, $holiday->month, $holiday->day);
                return $date->between($start, $end) ? $date->format('Y-m-d') : null;
            })
            ->filter() // Hapus nilai null
            ->values();

        // Ambil moveable holidays
        $moveableHolidays = DB::table('moveable_holidays')
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date');

        // Gabungkan dan urutkan semua tanggal libur
        return $fixedHolidays->merge($moveableHolidays)->unique()->sort()->values();
    }

    protected function generateHourlyConsumption($consumptions)
    {
        $minuteConsumptions = collect();

        // Langkah 1: Pisahkan data per menit
        foreach ($consumptions as $consumption) {
            $start = Carbon::parse($consumption->start_time);
            $end = Carbon::parse($consumption->end_time);
            $usage = $consumption->usage;

            // Menentukan total durasi dalam menit
            $totalDuration = $end->diffInMinutes($start);

            while ($start < $end) {
                $minuteEnd = $start->copy()->addMinute();

                if ($minuteEnd > $end) {
                    $minuteEnd = $end;
                }

                $intervalDuration = $minuteEnd->diffInMinutes($start);
                $intervalUsage = ($intervalDuration / $totalDuration) * $usage;

                $minuteConsumptions->push([
                    'start_time' => $start->format('Y-m-d H:i:s'),
                    'end_time' => $minuteEnd->format('Y-m-d H:i:s'),
                    'usage' => $intervalUsage,
                ]);

                $start = $minuteEnd;

                if ($start >= $end) {
                    break;
                }
            }
        }

        // Langkah 2: Agregasi data per jam
        $hourlyConsumptions = $minuteConsumptions->groupBy(function ($item) {
            return Carbon::parse($item['start_time'])->startOfHour()->format('Y-m-d H:00:00');
        })->map(function ($group) {
            $startTime = Carbon::parse($group->first()['start_time'])->startOfHour()->format('Y-m-d H:00:00');
            $endTime = Carbon::parse($group->last()['end_time'])->format('Y-m-d H:i:s');
            $totalUsage = $group->sum('usage');

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'usage' => $totalUsage,
            ];
        })->values();

        // Langkah 3: Menghitung accumulated_usage
        $dailyAccumulatedUsage = 0;
        $lastDate = null;

        $hourlyConsumptions = $hourlyConsumptions->map(function ($item) use (&$dailyAccumulatedUsage, &$lastDate) {
            $currentDate = Carbon::parse($item['start_time'])->format('Y-m-d');

            if ($lastDate !== $currentDate) {
                $dailyAccumulatedUsage = 0; // Reset daily accumulated usage
                $lastDate = $currentDate;
            }

            $dailyAccumulatedUsage += $item['usage'];
            $item['accumulated_usage'] = $dailyAccumulatedUsage;

            return $item;
        });

        return $hourlyConsumptions;
    }

    protected function calculateFixPerDay($calculationRatePlan, $chargeSubCategory, $startDate, $endDate)
    {
        // Convert startDate and endDate to Carbon instances
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Calculate the total usage as the difference in days plus one
        $totalUsage = $startDate->diffInDays($endDate) + 1;

        // Retrieve the first unit price
        $unitPrice = $chargeSubCategory->unitPrices->first()->price;


        // Create or update the calculation record
        $calculation = Calculation::updateOrCreate(
            [
                'calculation_rate_plan_id' => $calculationRatePlan->id,
                'charge_sub_category_id' => $chargeSubCategory->id,
            ],
            [
                'total_usage' => $totalUsage,
                'total_price' => $totalUsage * $unitPrice,
                'unit_price' => $unitPrice,
            ]
        );

        return $calculation;
    }

    protected function calculateFixPerMonth($calculationRatePlan, $chargeSubCategory, $hourlyConsumptions ,  $startDate, $endDate)
    {

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $total_days = $startDate->diffInDays($endDate) + 1;

        // Find the maximum usage from the hourly consumptions
        $totalUsage = collect($hourlyConsumptions)->max('usage');


        // Retrieve the first unit price
        $unitPrice = $chargeSubCategory->unitPrices->first()->price;

        // Create or update the calculation record
        $calculation = Calculation::updateOrCreate(
            [
                'calculation_rate_plan_id' => $calculationRatePlan->id,
                'charge_sub_category_id' => $chargeSubCategory->id,
            ],
            [
                'total_usage' => $totalUsage,
                'total_price' => $total_days / 30 * $totalUsage * $unitPrice,
                'unit_price' => $unitPrice,
            ]
        );

        return $calculation;
    }

    protected function calculatePeriod($calculationRatePlan, $chargeSubCategory, $hourlyConsumptions, $startDate, $endDate)
    {
        $totalUsage = 0;

        // Loop through hourly consumptions and calculate total usage
        foreach ($hourlyConsumptions as $consumption) {
            foreach ($chargeSubCategory->period->periodDetails as $periodDetail) {
                // Decode the JSON string of days into an array
                $days = json_decode($periodDetail->days);

                // Extract date and time components from consumption
                $consumptionDate = Carbon::parse($consumption['start_time'])->format('Y-m-d');
                $consumptionDay = Carbon::parse($consumption['start_time'])->format('l');
                $consumptionTime = Carbon::parse($consumption['start_time'])->format('H:i:s');

                // Check if the day of the consumption is within the period's defined days
                if (in_array($consumptionDay, $days)) {
                    $periodStartTime = Carbon::createFromFormat('H:i:s', $periodDetail->start_time);
                    $periodEndTime = Carbon::createFromFormat('H:i:s', $periodDetail->end_time);

                    // Check if the period crosses midnight
                    if ($periodStartTime > $periodEndTime) {
                        // Handle the case where the period crosses midnight
                        if (
                            ($consumptionTime >= $periodStartTime->format('H:i:s') ||
                            $consumptionTime <= $periodEndTime->format('H:i:s'))
                        ) {
                            $totalUsage += $consumption['usage'];
                        }
                    } else {
                        // Standard period within the same day
                        if ($consumptionTime >= $periodStartTime->format('H:i:s') &&
                            $consumptionTime <= $periodEndTime->format('H:i:s')) {
                            $totalUsage += $consumption['usage'];
                        }
                    }
                }
            }
        }

        // Retrieve the first unit price
        $unitPrice = $chargeSubCategory->unitPrices->first()->price;

        // Check for loss factor
        $lossFactor = $chargeSubCategory->loss_factor;


        // Calculate total price considering the loss factor
        $totalPrice = $totalUsage * $unitPrice;

        if ($lossFactor !== null) {
            $totalPrice *= $lossFactor;
        }
        // dd($totalPrice , $lossFactor);

        // Update or create the calculation record
        $calculation = Calculation::updateOrCreate(
            [
                'calculation_rate_plan_id' => $calculationRatePlan->id,
                'charge_sub_category_id' => $chargeSubCategory->id,
            ],
            [
                'total_usage' => $totalUsage,
                'total_price' => $totalPrice,
                'unit_price' => $unitPrice,
                'loss_factor' => $lossFactor,
            ]
        );

        return $calculation;
    }



}

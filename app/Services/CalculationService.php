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
            dd('m');
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

    protected function calculateFixPerDay2($calculation, $hourlyConsumptions)
    {
        // Retrieve the charge sub category and unit prices
        $chargeSubCategory = $calculation->chargeSubCategory;
        $unitPrices = $chargeSubCategory->unitPrices->sortBy('threshold');

        // Initialize variables
        $dailyUsage = 0;
        $totalPrice = 0;
        $previousThreshold = 0;

        // Aggregate hourly consumption per day
        $dailyConsumptions = $hourlyConsumptions->groupBy(function ($item) {
            return Carbon::parse($item['start_time'])->toDateString();
        });

        foreach ($dailyConsumptions as $day => $consumptions) {
            $dailyUsage = $consumptions->sum('usage');
            $price = 0;

            foreach ($unitPrices as $unitPrice) {
                $threshold = $unitPrice->threshold;
                $pricePerUnit = $unitPrice->price;

                // Calculate price based on threshold
                if ($dailyUsage > $threshold) {
                    // Price for usage up to the threshold
                    $usageAtThreshold = $threshold - $previousThreshold;
                    $price += $usageAtThreshold * $pricePerUnit;

                    // Update daily usage and previous threshold for the next threshold
                    $dailyUsage -= $usageAtThreshold;
                    $previousThreshold = $threshold;
                } else {
                    // Price for remaining usage within the threshold
                    $price += $dailyUsage * $pricePerUnit;
                    $dailyUsage = 0;
                    break;
                }
            }

            // Ensure to add remaining dailyUsage if there are more unitPrices
            if ($dailyUsage > 0) {
                $price += $dailyUsage * $pricePerUnit;
            }

            // Update calculation record
            $calculation->total_usage += $consumptions->sum('usage');
            $calculation->total_price += $price;

            // Reset for the next day
            $previousThreshold = 0;
        }

        // Save the updated calculation
        $calculation->save();
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
        dd($chargeSubCategory->unitPrices);
        foreach ($hourlyConsumptions as $consumption) {
            $consumptionStartTime = Carbon::parse($consumption['start_time']);
            $consumptionEndTime = Carbon::parse($consumption['end_time']);
            $consumptionDay = $consumptionStartTime->format('l'); // Get the day of the week
            $consumptionStartTimeOnly = $consumptionStartTime->format('H:i:s'); // Extract the time of consumption
            $consumptionEndTimeOnly = $consumptionEndTime->format('H:i:s');

            foreach ($chargeSubCategory->period->periodDetails as $periodDetail) {
                // Decode the JSON string of days into an array
                $days = json_decode($periodDetail->days);

                // Check if the day of the consumption is within the period's defined days
                if (in_array($consumptionDay, $days)) {
                    $periodStartTime = Carbon::createFromFormat('H:i:s', $periodDetail->start_time);
                    $periodEndTime = Carbon::createFromFormat('H:i:s', $periodDetail->end_time);

                    // Handle the case where the period crosses midnight
                    if ($periodStartTime > $periodEndTime) {
                        // Period that crosses midnight (e.g., 20:00 to 14:00)
                        if (
                            ($consumptionStartTimeOnly >= $periodStartTime->format('H:i:s') ||
                            $consumptionStartTimeOnly <= $periodEndTime->format('H:i:s'))
                        ) {
                            $totalUsage += $consumption['usage'];
                        }
                    } else {
                        // Standard period within the same day (e.g., 14:00 to 20:00)
                        if (
                            $consumptionStartTimeOnly >= $periodStartTime->format('H:i:s') &&
                            $consumptionEndTimeOnly <= $periodEndTime->format('H:i:s')
                        ) {
                            $totalUsage += $consumption['usage'];
                        }
                    }
                }
            }
        }

        Log::info($chargeSubCategory->name, ['total_usage' => $totalUsage]);
        // return $calculation;
    }


}

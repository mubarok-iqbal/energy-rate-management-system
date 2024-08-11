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
                    $calculationRatePlan = CalculationRatePlan::create([
                        'customer_id' => $customerId,
                        'rate_plan_id' => $ratePlanId,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);

                    // Ambil ChargeSubCategories yang aktif untuk ratePlan ini
                    $chargeSubCategories = $ratePlan->chargeCategories
                        ->where('is_active', true)
                        ->flatMap(function ($chargeCategory) use ($season) {
                            return $chargeCategory->chargeSubCategories->filter(function ($subChargeCategory) use ($season) {
                                return $subChargeCategory->is_active &&
                                    (!$subChargeCategory->season_id || $subChargeCategory->season_id == $season->id);
                            });
                        });

                    foreach ($chargeSubCategories as $sub) {

                        if($sub->calculation_type_id == CalculationType::FIX_PER_DAY){
                            dd($sub);
                        }
                        // Insert ke tabel calculations
                        $calculation = Calculation::create([
                            'calculation_rate_plan_id' => $calculationRatePlan->id,
                            'charge_sub_category_id' => $sub->id,
                            'total_usage' => 0, // Sementara total_usage = 0
                        ]);

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

        return $hourlyConsumptions;
    }
}

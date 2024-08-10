<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Retail;
use App\Models\CalculationRatePlan;
use App\Models\Calculation;
use App\Models\Consumption;
use App\Models\ChargeSubCategory;
use App\Models\Season;
use Carbon\Carbon;

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

            // Langkah 3: Tentukan musim yang berlaku
            $season = $this->getSeasonForDateRange($startDate, $endDate);

            // Langkah 4: Insert data ke tabel calculation_rate_plans
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

                    foreach ($chargeSubCategories as $subChargeCategory) {
                        // Insert ke tabel calculations
                        Calculation::create([
                            'calculation_rate_plan_id' => $calculationRatePlan->id,
                            'charge_sub_category_id' => $subChargeCategory->id,
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
}

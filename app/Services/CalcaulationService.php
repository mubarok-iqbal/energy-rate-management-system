<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\CalculationDetail;
use Carbon\Carbon;

class CalculationService
{
    /**
     * Create a new calculation entry.
     *
     * @param int $ratePlanId
     * @param int $customerId
     * @param string $startDate
     * @param string $endDate
     * @return Calculation
     */
    public function createCalculation(int $ratePlanId, int $customerId, string $startDate, string $endDate): Calculation
    {
        return Calculation::create([
            'rate_plan_id' => $ratePlanId,
            'customer_id' => $customerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Add calculation details for a specific calculation.
     *
     * @param Calculation $calculation
     * @param array $details
     * @return void
     */
    public function addCalculationDetails(Calculation $calculation, array $details): void
    {
        foreach ($details as $detail) {
            $calculation->calculationDetails()->create([
                'date' => $detail['date'],
                'start_time' => $detail['start_time'],
                'end_time' => $detail['end_time'],
                'usage' => $detail['usage'],
                'accumulated_usage' => $detail['accumulated_usage'],
            ]);
        }
    }

    /**
     * Example method to calculate accumulated usage.
     *
     * @param array $usages
     * @return float
     */
    public function calculateAccumulatedUsage(array $usages): float
    {
        return array_sum($usages);
    }
}

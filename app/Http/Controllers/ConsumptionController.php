<?php

namespace App\Http\Controllers;

use App\Models\CalculationRatePlan;
use App\Models\Customer;
use App\Models\Consumption;
use Illuminate\Http\Request;
use App\Services\CalculationService;

class ConsumptionController extends Controller
{
    protected $calculationService;

    public function __construct(CalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    public function index()
    {
        $consumptions = Consumption::with('customer')->get();

        $customers = Customer::all();

        return view('consumption.index', compact('consumptions' , 'customers'));
    }

    public function calculateUsage(Request $request)
    {
        // Validate the request
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        // Perform the calculation
        $this->calculationService->calculate($request->customer_id, $request->start_date, $request->end_date);

        // Redirect to the calculation result page
        return redirect()->route('calculation.detail', [
            'customer_id' => $request->customer_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    public function getCalculation($customerId, $startDate, $endDate)
    {
        $calculationRatePlan = CalculationRatePlan::where('customer_id', $customerId)
            ->where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->with(['calculations.chargeSubCategory.chargeCategory']) // Eager load relationships
            ->get()
            ->map(function ($ratePlan) {
                // Group calculations by chargeCategory
                $ratePlan->calculationsGrouped = $ratePlan->calculations->groupBy(function ($calculation) {
                    return $calculation->chargeSubCategory->chargeCategory->id;
                });
                return $ratePlan;
            });

        return view('calculation.result', compact('calculationRatePlan'));
    }
}

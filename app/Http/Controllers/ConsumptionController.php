<?php

namespace App\Http\Controllers;

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
        $calculationId = $this->calculationService->calculate($request->customer_id, $request->start_date, $request->end_date);

        // Redirect to the calculation result page
        return redirect()->route('calculation.result', ['id' => $calculationId]);
    }

    public function calculationResult($id)
    {
        $calculation = Calculation::with('details')->findOrFail($id);
        return view('calculation-result', compact('calculation'));
    }


}

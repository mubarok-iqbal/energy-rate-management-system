<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Consumption;
use Illuminate\Http\Request;

class ConsumptionController extends Controller
{
    public function index()
    {
        $consumptions = Consumption::with('customer')->get();

        $customers = Customer::all();

        return view('consumption.index', compact('consumptions' , 'customers'));
    }
}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3>Calculation Result</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Customer:</strong></div>
                <div class="col-md-9">{{ $customer->name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Start Date:</strong></div>
                <div class="col-md-9">{{ $startDate }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>End Date:</strong></div>
                <div class="col-md-9">{{ $endDate }}</div>
            </div>
        </div>
    </div>

    @if(isset($calculationRatePlan) && $calculationRatePlan->count() > 0)
        @foreach($calculationRatePlan as $ratePlan)
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5>{{ $ratePlan->ratePlan->name }}</h5>
                    <span>{{ $ratePlan->ratePlan->retail->name }}</span>
                </div>

                <div class="card-body">
                    @php
                        $totalPriceForRatePlan = 0; // Variabel untuk menyimpan total price
                    @endphp
                    @foreach($ratePlan->calculationsGrouped as $chargeCategoryId => $calculations)
                        @php
                            $chargeCategory = $calculations->first()->chargeSubCategory->chargeCategory;
                        @endphp
                        <h6 class="bg-light p-2 rounded">{{ $chargeCategory->name }}</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center text-nowrap">Description</th>
                                        <th class="text-center text-nowrap">Usage</th>
                                        <th class="text-center text-nowrap">Loss Factor</th>
                                        <th class="text-center text-nowrap">Unit Price</th>
                                        <th class="text-center text-nowrap">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calculations as $calculation)
                                        @php
                                            $totalPriceForRatePlan += $calculation->total_price; // Tambahkan total_price ke total
                                        @endphp
                                        <tr>
                                            <td class="text-nowrap">{{ $calculation->chargeSubCategory->name }}</td>
                                            <td class="text-end">{{ number_format($calculation->total_usage, 6) }}</td>
                                            <td class="text-end">{{ $calculation->loss_factor }}</td>
                                            <td class="text-end">{{ $calculation->unit_price  }}</td>
                                            <td class="text-end">{{ number_format($calculation->total_price, 6) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    <div class="mt-3 d-flex justify-content-between w-100">
                        <div>
                            <span class="h5">Total Price for {{ $ratePlan->ratePlan->name }}:</span>
                        </div>
                        <div>
                            <span class="h5">{{ number_format($totalPriceForRatePlan, 6) }}</span>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning" role="alert">
            No calculation results found.
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ url('/') }}" class="btn btn-success">
            Back
        </a>
    </div>
</div>
@endsection

@section('scripts')
<!-- You can include custom scripts here if needed -->
@endsection

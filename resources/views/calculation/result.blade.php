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
                    @foreach($ratePlan->calculationsGrouped as $chargeCategoryId => $calculations)
                        @php
                            $chargeCategory = $calculations->first()->chargeSubCategory->chargeCategory;
                        @endphp
                        <h6 class="bg-light p-2 rounded">{{ $chargeCategory->name }}</h6>
                        <table class="table table-bordered table-hover mt-3">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Description</th>
                                    <th>Usage</th>
                                    <th>Loss Factor</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calculations as $calculation)
                                    <tr>
                                        <td>{{ $calculation->chargeSubCategory->name }}</td>
                                        <td>{{ number_format($calculation->total_usage, 6) }}</td>
                                        <td>{{ $calculation->chargeSubCategory->lossFactor }}</td>
                                        <td>{{ $calculation->chargeSubCategory->calculation_type_id == 1 ? $calculation->chargeSubCategory->unitPrices->first()->price : '-' }}</td>
                                        <td>{{ number_format($calculation->total_price, 6) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning" role="alert">
            No calculation results found.
        </div>

        <!-- Tombol Back -->
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

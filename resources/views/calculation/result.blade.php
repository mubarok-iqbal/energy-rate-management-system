@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Calculation Result</h1>

    @if(isset($calculationRatePlan) && $calculationRatePlan->count() > 0)
        @foreach($calculationRatePlan as $ratePlan)
            <div class="card mb-3">
                <div class="card-header">
                    <h3>{{ $ratePlan->ratePlan->name }}</h3>
                </div>
                <div class="card-body">
                    @foreach($ratePlan->calculationsGrouped as $chargeCategoryId => $calculations)
                        @php
                            $chargeCategory = $calculations->first()->chargeSubCategory->chargeCategory;
                        @endphp
                        <h4>{{ $chargeCategory->name }}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Usage</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calculations as $calculation)
                                    <tr>
                                        <td>{{ $calculation->chargeSubCategory->name }}</td>
                                        <td>{{ number_format($calculation->total_usage, 6) }}</td>
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
        <p>No calculation results found.</p>
    @endif
</div>
@endsection

@section('scripts')
<!-- You can include custom scripts here if needed -->
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <!-- <h3 class="mb-4">Consumption Data</h3> -->

    <!-- Green Calculate Usage Button aligned to the right -->
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#calculateModal">
            Calculate Usage
        </button>
    </div>

    <!-- Card for DataTables Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Consumption Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="consumption-table" class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Usage (kWH)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consumptions as $consumption)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $consumption->customer->name }}</td>
                                <td>{{ $consumption->start_time }}</td>
                                <td>{{ $consumption->end_time }}</td>
                                <td>{{ $consumption->usage }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('modals.calculate')

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#consumption-table').DataTable();

        // Initialize Select2 when the modal is shown
        $('#calculateModal').on('shown.bs.modal', function () {
            $('#customer_name').select2({
                dropdownParent: $('#calculateModal')
            });
        });
    });
</script>
@endsection

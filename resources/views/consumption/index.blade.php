@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Consumption Data</h1>

    <!-- Green Calculate Usage Button aligned to the right -->
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#calculateModal">
            Calculate Usage
        </button>
    </div>

    <table id="consumption-table" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Usage</th>
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

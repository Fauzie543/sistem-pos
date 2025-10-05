@extends('layouts.app')
@section('title', 'Kendaraan')
@section('header')
    Vehicles for: {{ $customer->name }}
@endsection

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm mb-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="font-semibold text-gray-800">Customer Details</h3>
            <p class="text-sm text-gray-600">Name: {{ $customer->name }}</p>
            <p class="text-sm text-gray-600">Phone: {{ $customer->phone_number }}</p>
        </div>
        <div class="text-right">
            <a href="{{ route('customers.index') }}" class="text-blue-600 hover:underline">&larr; Back to Customers List</a>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addVehicleBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah Kendaraan
    </button>

    <table id="vehicles-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>No Kendaraan</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Tahun</th>
                <th class="w-32">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@include('vehicles.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    var table = $('#vehicles-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('vehicles.data', $customer->id) }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'license_plate', name: 'license_plate' },
            { data: 'brand', name: 'brand' },
            { data: 'model', name: 'model' },
            { data: 'year', name: 'year' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    $('#addVehicleBtn').on('click', function () {
        $('#vehicleForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Tambah Kendaraan');
        $('#submitBtn').text('Simpan');
        $('#form_method').val('POST');
        $('#vehicleForm').attr('action', '{{ route('vehicles.store') }}');
        $('#vehicleModal').removeClass('hidden');
    });

    $('#vehicles-table').on('click', '.edit-btn', function () {
        var vehicleId = $(this).data('id');
        var url = `/vehicles/${vehicleId}/edit`;

        $('#vehicleForm')[0].reset();
        $('.error-message').text('');
        
        $.get(url, function(data) {
            $('#modal_title').text('Edit Kendaraan');
            $('#submitBtn').text('Simpan Perubahan');
            $('#form_method').val('PUT');
            $('#vehicleForm').attr('action', `/vehicles/${vehicleId}`);
            $('#license_plate').val(data.license_plate);
            $('#brand').val(data.brand);
            $('#model').val(data.model);
            $('#year').val(data.year);
            $('#vehicleModal').removeClass('hidden');
        });
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#vehicleModal').addClass('hidden');
    });

    $('#vehicleForm').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#vehicleModal').addClass('hidden');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.success, showConfirmButton: false, timer: 3000 });
                table.ajax.reload();
            },
            error: function (xhr) {
                $('.error-message').text('');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key + '_error').text(value[0]);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong!' });
                }
            }
        });
    });
    
    $('#vehicles-table').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.success, showConfirmButton: false, timer: 3000 });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Failed!', xhr.responseJSON.error || 'There was a problem.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
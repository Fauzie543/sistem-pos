@extends('layouts.app')

@section('header', 'Customers Management')

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addCustomerBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Add Customer
    </button>

    <table id="customers-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th class="w-48">Action</th>
            </tr>
        </thead>
    </table>
</div>

@include('customers.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    var table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('customers.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'phone_number', name: 'phone_number' },
            { data: 'address', name: 'address' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
    });

    $('#addCustomerBtn').on('click', function () {
        $('#customerForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Add New Customer');
        $('#submitBtn').text('Save Customer');
        $('#form_method').val('POST');
        $('#customerForm').attr('action', '{{ route('customers.store') }}');
        $('#customerModal').removeClass('hidden');
    });

    $('#customers-table').on('click', '.edit-btn', function () {
        var customerId = $(this).data('id');
        var url = `/customers/${customerId}/edit`;
        
        $('#customerForm')[0].reset();
        $('.error-message').text('');

        $.get(url, function(data) {
            $('#modal_title').text('Edit Customer');
            $('#submitBtn').text('Update Customer');
            $('#form_method').val('PUT');
            $('#customerForm').attr('action', `/customers/${customerId}`);
            $('#name').val(data.name);
            $('#phone_number').val(data.phone_number);
            $('#address').val(data.address);
            $('#customerModal').removeClass('hidden');
        });
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#customerModal').addClass('hidden');
    });

    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#customerModal').addClass('hidden');
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

    $('#customers-table').on('click', '.delete-btn', function (e) {
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
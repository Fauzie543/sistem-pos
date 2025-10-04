@extends('layouts.app')

@section('header', 'Suppliers Management')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }
</style>
@endpush

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addSupplierBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Add Supplier
    </button>

    <table id="suppliers-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Contact Person</th>
                <th class="w-32">Action</th>
            </tr>
        </thead>
    </table>
</div>

@include('suppliers.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    var table = $('#suppliers-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{{ route('suppliers.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'phone_number', name: 'phone_number' },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    $('#addSupplierBtn').on('click', function () {
        $('#supplierForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Add New Supplier');
        $('#submitBtn').text('Save Supplier');
        $('#form_method').val('POST');
        $('#supplierForm').attr('action', '{{ route('suppliers.store') }}');
        $('#supplierModal').removeClass('hidden');
    });

    $('#suppliers-table').on('click', '.edit-btn', function () {
        var supplierId = $(this).data('id');
        var url = `/suppliers/${supplierId}/edit`;

        $('#supplierForm')[0].reset();
        $('.error-message').text('');

        $.get(url, function(data) {
            $('#modal_title').text('Edit Supplier');
            $('#submitBtn').text('Update Supplier');
            $('#form_method').val('PUT');
            $('#supplierForm').attr('action', `/suppliers/${supplierId}`);
            $('#name').val(data.name);
            $('#phone_number').val(data.phone_number);
            $('#contact_person').val(data.contact_person);
            $('#address').val(data.address);
            $('#supplierModal').removeClass('hidden');
        }).fail(function() {
            Swal.fire('Error', 'Could not fetch supplier data.', 'error');
        });
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#supplierModal').addClass('hidden');
    });

    $('#supplierForm').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#supplierModal').addClass('hidden');
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
    
    $('#suppliers-table').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            text: "You won't be able to revert this!",
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
@extends('layouts.app')

@section('header', 'Products Management')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }
</style>
@endpush

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addProductBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Add Product
    </button>

    <table id="products-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Name</th>
                <th>Category</th>
                <th>Selling Price</th>
                <th class="w-16">Stock</th>
                <th class="w-32">Action</th>
            </tr>
        </thead>
    </table>
</div>

@include('products.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{{ route('products.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'category.name', name: 'category.name' },
            { data: 'selling_price', name: 'selling_price', className: 'text-right' },
            { data: 'stock', name: 'stock', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    $('#addProductBtn').on('click', function () {
        $('#productForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Add New Product');
        $('#submitBtn').text('Save Product');
        $('#form_method').val('POST');
        $('#productForm').attr('action', '{{ route('products.store') }}');
        $('#productModal').removeClass('hidden');
    });

    $('#products-table').on('click', '.edit-btn', function () {
        var productId = $(this).data('id');
        var url = `/products/${productId}/edit`;

        $('#productForm')[0].reset();
        $('.error-message').text('');

        $.get(url, function(data) {
            $('#modal_title').text('Edit Product');
            $('#submitBtn').text('Update Product');
            $('#form_method').val('PUT');
            $('#productForm').attr('action', `/products/${productId}`);

            // Isi semua field form
            $.each(data, function(key, value) {
                $('#' + key).val(value);
            });

            $('#productModal').removeClass('hidden');
        }).fail(function() {
            Swal.fire('Error', 'Could not fetch product data.', 'error');
        });
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#productModal').addClass('hidden');
    });

    $('#productForm').on('submit', function (e) {
        e.preventDefault();
        $('.error-message').text('');
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#productModal').addClass('hidden');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.success, showConfirmButton: false, timer: 3000 });
                table.ajax.reload();
            },
            error: function (xhr) {
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
    
    $('#products-table').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
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
@extends('layouts.app')
@section('title', 'Service')
@section('header', 'Manajemen Service')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }
</style>
@endpush

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addServiceBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah Service
    </button>

    <table id="services-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th class="w-32">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@include('services.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    var table = $('#services-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{{ route('services.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'category.name', name: 'category.name' },
            { data: 'price', name: 'price', className: 'text-right' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    $('#addServiceBtn').on('click', function () {
        $('#serviceForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Tambah Service');
        $('#submitBtn').text('Simpan');
        $('#form_method').val('POST');
        $('#serviceForm').attr('action', '{{ route('services.store') }}');
        $('#serviceModal').removeClass('hidden');
    });

    $('#services-table').on('click', '.edit-btn', function () {
        var serviceId = $(this).data('id');
        var url = `/services/${serviceId}/edit`;

        $('#serviceForm')[0].reset();
        $('.error-message').text('');

        $.get(url, function(data) {
            $('#modal_title').text('Edit Service');
            $('#submitBtn').text('Simpan Perubahan');
            $('#form_method').val('PUT');
            $('#serviceForm').attr('action', `/services/${serviceId}`);
            $('#name').val(data.name);
            $('#category_id').val(data.category_id);
            $('#price').val(data.price);
            $('#serviceModal').removeClass('hidden');
        }).fail(function() {
            Swal.fire('Error', 'Could not fetch service data.', 'error');
        });
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#serviceModal').addClass('hidden');
    });

    $('#serviceForm').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#serviceModal').addClass('hidden');
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
    
    $('#services-table').on('click', '.delete-btn', function (e) {
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
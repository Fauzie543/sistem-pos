@extends('layouts.app')
@section('title', 'Kategori')
@section('header', 'Manajemen Kategori')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }
</style>
@endpush

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addCategoryBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah Kategori
    </button>

    <table id="categories-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th class="w-32">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@include('categories.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // ## 1. Inisialisasi DataTables
    var table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{{ route('categories.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    // ## 2. Logika Buka Modal (Tambah Data)
    $('#addCategoryBtn').on('click', function () {
        $('#categoryForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Tambah Kategori');
        $('#submitBtn').text('Simpan');
        $('#form_method').val('POST');
        $('#categoryForm').attr('action', '{{ route('categories.store') }}');
        $('#categoryModal').removeClass('hidden');
    });

    // ## 3. Logika Buka Modal (Edit Data)
    $('#categories-table').on('click', '.edit-btn', function () {
        var categoryId = $(this).data('id');
        var url = `/categories/${categoryId}/edit`;

        $('#categoryForm')[0].reset();
        $('.error-message').text('');

        $.get(url, function(data) {
            $('#modal_title').text('Edit Kategori');
            $('#submitBtn').text('Simpan Perubahan');
            $('#form_method').val('PUT');
            $('#categoryForm').attr('action', `/categories/${categoryId}`);

            $('#category_id').val(data.id);
            $('#name').val(data.name);
            $('#description').val(data.description);

            $('#categoryModal').removeClass('hidden');
        }).fail(function() {
            Swal.fire('Error', 'Could not fetch category data.', 'error');
        });
    });

    // ## 4. Logika Tutup Modal
    $('#cancelBtn, .close-modal').on('click', function () {
        $('#categoryModal').addClass('hidden');
    });

    // ## 5. Logika Submit Form (AJAX)
    $('#categoryForm').on('submit', function (e) {
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
                $('#categoryModal').addClass('hidden');
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
    
    // ## 6. Logika Delete
    $('#categories-table').on('click', '.delete-btn', function (e) {
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
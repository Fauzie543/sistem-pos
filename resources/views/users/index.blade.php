@extends('layouts.app')
@section('title', 'Users')
@section('header', 'Manajemen User')


@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addUserBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah User
    </button>

    <table id="users-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th class="w-32">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@include('users.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    var table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('users.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'role.name', name: 'role.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    // Buka Modal Tambah
    $('#addUserBtn').on('click', function () {
        $('#userForm')[0].reset();
        $('.error-message').text('');
        $('#password_fields').show();
        $('#modal_title').text('Tambah User Baru');
        $('#submitBtn').text('Simpan');
        $('#form_method').val('POST');
        $('#userForm').attr('action', '{{ route('users.store') }}');
        $('#userId').val('');
        $('#userModal').removeClass('hidden');
    });

    // Buka Modal Edit
    $('#users-table').on('click', '.edit-btn', function () {
        var userId = $(this).data('id');
        var url = `/users/${userId}/edit`;

        $('#userForm')[0].reset();
        $('.error-message').text('');
        $('#password_fields').hide(); // Sembunyikan field password saat edit
        
        $.get(url, function(data) {
            $('#modal_title').text('Edit User');
            $('#submitBtn').text('Simpan Perubahan');
            $('#form_method').val('PUT');
            $('#userForm').attr('action', `/users/${userId}`);
            $('#userId').val(data.id);
            
            $('#name').val(data.name);
            $('#email').val(data.email);
            $('#role_id').val(data.role_id);
            $('#password_info').text('Kosongkan jika tidak ingin mengubah password.');

            $('#userModal').removeClass('hidden');
        });
    });

    // Tutup Modal
    $('#cancelBtn, .close-modal').on('click', function () {
        $('#userModal').addClass('hidden');
    });

    // Submit Form (Tambah/Edit)
    $('#userForm').on('submit', function (e) {
        e.preventDefault();
        $('.error-message').text('');
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST', // Method tetap POST, _method=PUT akan dihandle Laravel
            data: formData,
            success: function (response) {
                $('#userModal').addClass('hidden');
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
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong.' });
                }
            }
        });
    });
    
    // Hapus Data
    $('#users-table').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
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
                        Swal.fire('Gagal!', xhr.responseJSON.error || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
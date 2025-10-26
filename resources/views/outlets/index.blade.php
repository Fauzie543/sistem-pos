@extends('layouts.app')
@section('title', 'Cabang / Outlet')
@section('header', 'Manajemen Cabang / Outlet')

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addOutletBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
        Tambah Outlet
    </button>

    <table id="outlets-table" class="w-full">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Outlet</th>
                <th>Kode</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@include('outlets.modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    const table = $('#outlets-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('outlets.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'address', name: 'address' },
            { data: 'phone', name: 'phone' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    $('#addOutletBtn').on('click', function () {
        $('#outletForm')[0].reset();
        $('#modal_title').text('Tambah Outlet');
        $('#form_method').val('POST');
        $('#outletModal').removeClass('hidden');
        $('#outletForm').attr('action', '{{ route('outlets.store') }}');
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#outletModal').addClass('hidden');
    });

    $('#outletForm').on('submit', function (e) {
        e.preventDefault();
        const url = $(this).attr('action');
        const formData = $(this).serialize();

        $.ajax({
            url, method: 'POST', data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: (res) => {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.success, showConfirmButton: false, timer: 3000 });
                $('#outletModal').addClass('hidden');
                table.ajax.reload();
            },
            error: (xhr) => Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan', 'error')
        });
    });
});
</script>
@endpush

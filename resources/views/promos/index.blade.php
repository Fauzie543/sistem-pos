@extends('layouts.app')
@section('title', 'Diskon & Promo')
@section('header', 'Manajemen Diskon & Promo')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }
</style>
@endpush

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addPromoBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah Promo
    </button>

    <table id="promos-table" class="w-full">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Promo</th>
                <th>Jenis Diskon</th>
                <th>Nilai Diskon</th>
                <th>Periode</th>
                <th>Produk</th>
                <th>Status</th>
            </tr>
        </thead>
    </table>
</div>

@include('promos.modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    const table = $('#promos-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('promos.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'type', name: 'type', render: function (data) {
                return data === 'percent' ? 'Persentase (%)' : 'Nominal (Rp)';
            }},
            { data: 'value', name: 'value', className: 'text-right' },
            { data: null, name: 'periode', render: function (data) {
                return `${data.start_date} s/d ${data.end_date}`;
            }},
            { data: 'products', name: 'products', defaultContent: '-', render: data => data || '-' },
            { data: 'is_active', name: 'is_active', className: 'text-center', render: function (data) {
                return data ? '<span class="text-green-600 font-semibold">Aktif</span>' : '<span class="text-gray-400">Tidak Aktif</span>';
            }},
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    // === Modal Logic ===
    $('#addPromoBtn').on('click', function () {
        $('#promoForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Tambah Promo');
        $('#promoModal').removeClass('hidden');
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#promoModal').addClass('hidden');
    });

    $('#promoForm').on('submit', function (e) {
        e.preventDefault();
        const url = '{{ route('promos.store') }}';
        const formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#promoModal').addClass('hidden');
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
                    Swal.fire('Error', 'Terjadi kesalahan!', 'error');
                }
            }
        });
    });
});
</script>
@endpush

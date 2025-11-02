@extends('layouts.app')
@section('title', 'Diskon & Promo')
@section('header', 'Manajemen Diskon & Promo')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }

    /* === Select2 - Gaya konsisten dengan input Tailwind === */
    .select2-container--default .select2-selection--multiple {
        display: flex !important;
        align-items: center !important;
        min-height: 42px !important; /* ✅ Samakan tinggi input lain */
        border: 1px solid #d1d5db !important; /* border-gray-300 */
        border-radius: 0.375rem !important; /* rounded-md */
        padding: 4px 8px !important;
        background-color: #fff !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }

    /* Fokus border biru */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6 !important; /* blue-500 */
        box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }

    /* Tag produk */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #3b82f6 !important; /* blue-500 */
        border: none !important;
        color: white !important;
        border-radius: 0.375rem !important;
        padding: 2px 8px !important;
        margin-top: 4px !important;
        margin-right: 4px !important;
        font-size: 0.875rem !important;
    }

    /* Tombol hapus tag */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        margin-right: 4px !important;
        font-weight: bold !important;
    }

    /* Field input pencarian */
    .select2-container--default .select2-search--inline .select2-search__field {
        margin-top: 4px !important;
        font-size: 0.875rem !important;
        color: #374151 !important; /* gray-700 */
        height: 30px !important;
    }

    /* Dropdown */
    .select2-dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        padding: 4px;
    }

    /* Item hasil */
    .select2-results__option {
        padding: 8px 10px !important;
        border-radius: 0.375rem !important;
        cursor: pointer;
    }

    /* Hover item */
    .select2-results__option--highlighted {
        background-color: #eff6ff !important; /* blue-50 */
        color: #1e3a8a !important; /* blue-900 */
    }

    /* Pastikan tinggi container luar ikut */
    .select2-container {
        width: 100% !important;
    }
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
$(document).ready(function() {
    // === Inisialisasi Select2 ===
    $('#products').select2({
        placeholder: 'Pilih satu atau beberapa produk...',
        width: '100%',
        allowClear: true
    });

    // Reset select2 setiap kali modal dibuka
    $('#addPromoBtn').on('click', function() {
        $('#products').val(null).trigger('change');
        $('#promoModal').removeClass('hidden');
    });

    // Tutup modal
    $('#cancelBtn, .close-modal').on('click', function() {
        $('#promoModal').addClass('hidden');
    });
});
</script>

<script>
$(function () {
    const table = $('#promos-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('promos.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            {
                data: 'type',
                name: 'type',
                render: function (data) {
                    return data === 'percent' ? 'Persentase (%)' : 'Nominal (Rp)';
                }
            },
            {
                data: 'value',
                name: 'value',
                className: 'text-right',
                render: function (data, type, row) {
                    const value = parseFloat(data);
                    if (isNaN(value)) return '-';

                    if (row.type === 'percent') {
                        return Number.isInteger(value)
                            ? `${value}%`
                            : `${value.toFixed(2)}%`;
                    } else {
                        return 'Rp ' + new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(value);
                    }
                }
            },
            {
                data: null,
                name: 'periode',
                render: function (data) {
                    const start = new Date(data.start_date);
                    const end = new Date(data.end_date);

                    const options = { day: '2-digit', month: 'short', year: 'numeric' };

                    const startFormatted = start.toLocaleDateString('id-ID', options).replace('.', '');
                    const endFormatted = end.toLocaleDateString('id-ID', options).replace('.', '');

                    return `${startFormatted} s/d ${endFormatted}`;
                }
            },
            { data: 'products', name: 'products', defaultContent: '-', render: data => data || '-' },
            {
                data: 'is_active',
                name: 'is_active',
                className: 'text-center',
                render: function (data) {
                    return data
                        ? '<span class="text-green-600 font-semibold">Aktif</span>'
                        : '<span class="text-gray-400">Tidak Aktif</span>';
                }
            },
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

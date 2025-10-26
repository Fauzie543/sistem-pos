@extends('layouts.app')
@section('title', 'Riwayat Penjualan')
@section('header', 'Riwayat Penjualan')

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <table id="sales-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>No Resi</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Kasir</th>
                <th class="w-32">Action</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // === 1️⃣ Inisialisasi DataTables ===
    let table = $('#sales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('sales.history.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'created_at', name: 'created_at' },
            { data: 'customer.name', name: 'customer.name' },
            { data: 'total_amount', name: 'total_amount', className: 'text-right' },
            { data: 'user.name', name: 'user.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: '<"flex justify-between items-center mb-4"l<"flex items-center"f<"export-button-placeholder ml-3">>>rt<"flex justify-between items-center mt-4"ip>'
    });

    // === 2️⃣ Isi "placeholder" untuk tombol & filter ===
    const currentYear = new Date().getFullYear();
    const months = [
        {value: '', text: 'Semua Bulan'},
        {value: 1, text: 'Januari'}, {value: 2, text: 'Februari'}, {value: 3, text: 'Maret'},
        {value: 4, text: 'April'}, {value: 5, text: 'Mei'}, {value: 6, text: 'Juni'},
        {value: 7, text: 'Juli'}, {value: 8, text: 'Agustus'}, {value: 9, text: 'September'},
        {value: 10, text: 'Oktober'}, {value: 11, text: 'November'}, {value: 12, text: 'Desember'}
    ];

    let bulanOptions = months.map(m => `<option value="${m.value}">${m.text}</option>`).join('');
    let tahunOptions = `<option value="">Semua Tahun</option>`;
    for (let y = currentYear; y >= currentYear - 5; y--) {
        tahunOptions += `<option value="${y}">${y}</option>`;
    }

    @php
        $company = auth()->user()->company;
        $isBasic = !$company->featureEnabled('inventory_control') && !$company->featureEnabled('employee_management');
    @endphp

    @if ($isBasic)
        $('div.export-button-placeholder').html(`
            <button id="exportExcelBtn"
                class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-1.5 px-3 rounded transition">
                <i class="fas fa-file-excel mr-1"></i> Export Laporan Harian
            </button>
        `);
    @else
        $('div.export-button-placeholder').html(`
            <select id="filterBulan" class="border border-gray-300 text-sm rounded px-2 py-1 mr-2">
                ${bulanOptions}
            </select>
            <select id="filterTahun" class="border border-gray-300 text-sm rounded px-2 py-1 mr-2">
                ${tahunOptions}
            </select>
            <button id="exportExcelBtn"
                class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-1.5 px-3 rounded transition">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
        `);
    @endif

    // === 3️⃣ Event: Export berdasarkan filter bulan & tahun ===
    $(document).on('click', '#exportExcelBtn', function() {
        const bulan = $('#filterBulan').val();
        const tahun = $('#filterTahun').val();

        // arahkan ke route export dengan query string
        let url = "{{ route('sales.export.excel') }}" + '?';
        if (bulan) url += `bulan=${bulan}&`;
        if (tahun) url += `tahun=${tahun}`;

        window.location.href = url;
    });
});
</script>
@endpush

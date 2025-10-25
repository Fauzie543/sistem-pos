@extends('layouts.app')
@section('title', 'Pembelian')
@section('header', 'Transaksi Pembelian')

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <a href="{{ route('purchases.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah Pembelian
    </a>

    {{-- Tempat DataTables --}}
    <table id="purchases-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Total Harga</th>
                <th>Recorded By</th>
                <th class="w-32">Aksi</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@push('scripts')
{{-- ✅ Tambahkan SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // === Inisialisasi DataTables ===
    let table = $('#purchases-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('purchases.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'purchase_date', name: 'purchase_date' },
            { data: 'supplier.name', name: 'supplier.name' },
            { data: 'total_amount', name: 'total_amount', className: 'text-right' },
            { data: 'user.name', name: 'user.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    // === Tampilkan alert sukses setelah redirect (Laravel session) ===
    @if (session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000
        });
    @endif

    // === Tampilkan alert error jika ada (misalnya gagal create purchase) ===
    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: "{{ session('error') }}"
        });
    @endif
});
</script>
@endpush

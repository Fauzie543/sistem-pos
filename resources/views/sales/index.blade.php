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
$(function () {
    $('#sales-table').DataTable({
        processing: true, serverSide: true,
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
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });
});
</script>
@endpush
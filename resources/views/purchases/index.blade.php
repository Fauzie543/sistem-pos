@extends('layouts.app')

@section('header', 'Purchase Transactions')

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <a href="{{ route('purchases.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Create New Purchase
    </a>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <table id="purchases-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Total Amount</th>
                <th>Recorded By</th>
                <th class="w-32">Action</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#purchases-table').DataTable({
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
    });
});
</script>
@endpush
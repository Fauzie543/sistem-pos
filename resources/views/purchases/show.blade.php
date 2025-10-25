@extends('layouts.app')

@section('header', 'Purchase Details')

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 border-b pb-4">
        <div>
            <h3 class="font-semibold">Supplier</h3>
            <p>{{ $purchase->supplier->name }}</p>
            <p>{{ $purchase->supplier->phone_number }}</p>
        </div>
        <div>
            <h3 class="font-semibold">Transaction Info</h3>
            <p>Date: {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d F Y') }}</p>
            <p>Invoice No: {{ $purchase->invoice_number ?? '-' }}</p>
        </div>
        <div>
            <h3 class="font-semibold">Recorded By</h3>
            <p>{{ $purchase->user->name }}</p>
        </div>
    </div>

    <h3 class="text-lg font-medium mb-2">Purchased Items</h3>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">Product</th>
                <th class="w-32 text-center py-2">Quantity</th>
                <th class="w-48 text-right py-2">Price</th>
                <th class="w-48 text-right py-2">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->details as $detail)
            <tr class="border-b">
                <td class="py-2">{{ $detail->product->name }}</td>
                <td class="text-center">{{ $detail->quantity }}</td>
                <td class="text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="3" class="text-right py-3">Grand Total</td>
                <td class="text-right py-3">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="flex justify-end mt-6">
        <a href="{{ route('purchases.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            &larr; Back to Purchases List
        </a>
    </div>
</div>
@endsection
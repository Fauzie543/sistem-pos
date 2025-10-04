@extends('layouts.app')

@section('header', 'Detail Penjualan: ' . $sale->invoice_number)

@push('styles')
{{-- Style ini akan menyembunyikan elemen yang tidak perlu saat halaman dicetak --}}
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div id="print-area" class="bg-white p-6 rounded-md shadow-sm">
    {{-- Header Struk --}}
    <div class="flex justify-between items-start border-b pb-4 mb-4">
        <div>
            <h2 class="text-2xl font-bold">{{ config('app.name', 'BengkelPOS') }}</h2>
            <p class="text-sm text-gray-600">Jl. Teknik Kimia, Surabaya, Indonesia</p>
            <p class="text-sm text-gray-600">telepon: (031) 123-4567</p>
        </div>
        <div class="text-right">
            <h3 class="text-xl font-semibold">INVOICE</h3>
            <p class="text-sm"><strong>No:</strong> {{ $sale->invoice_number }}</p>
            <p class="text-sm"><strong>Tanggal:</strong> {{ $sale->created_at->format('d F Y H:i') }}</p>
        </div>
    </div>

    {{-- Info Pelanggan & Kasir --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <h3 class="font-semibold text-gray-800">Pelanggan:</h3>
            <p class="text-sm">{{ $sale->customer->name }}</p>
            <p class="text-sm">{{ $sale->customer->phone_number }}</p>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Kendaraan:</h3>
            @if($sale->vehicle)
                <p class="text-sm">{{ $sale->vehicle->license_plate }} ({{ $sale->vehicle->brand }} {{ $sale->vehicle->model }})</p>
            @else
                <p class="text-sm">-</p>
            @endif
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Kasir:</h3>
            <p class="text-sm">{{ $sale->user->name }}</p>
        </div>
    </div>

    {{-- Tabel Item --}}
    <h3 class="text-lg font-medium mb-2">Rincian Item:</h3>
    <div class="relative overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr class="border-b">
                    <th class="text-left py-2 px-4">Item</th>
                    <th class="w-24 text-center py-2 px-4">Qty</th>
                    <th class="w-40 text-right py-2 px-4">Harga Satuan</th>
                    <th class="w-40 text-right py-2 px-4">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $detail)
                <tr class="border-b">
                    <td class="py-2 px-4">
                        {{ $detail->product->name ?? $detail->service->name }}
                        <span class="text-xs text-gray-500">{{ $detail->product ? '[P]' : '[J]' }}</span>
                    </td>
                    <td class="text-center py-2 px-4">{{ $detail->quantity }}</td>
                    <td class="text-right py-2 px-4">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td class="text-right py-2 px-4">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold bg-gray-50">
                    <td colspan="3" class="text-right py-3 px-4">Grand Total</td>
                    <td class="text-right py-3 px-4 text-lg">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                 <tr class="font-semibold">
                    <td colspan="3" class="text-right py-2 px-4">Metode Pembayaran</td>
                    <td class="text-right py-2 px-4">{{ Str::upper($sale->payment_method) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    {{-- Footer Struk --}}
    <div class="text-center mt-8 text-sm text-gray-600">
        <p>Terima kasih telah melakukan servis di bengkel kami.</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
    </div>

</div>

{{-- Tombol Aksi --}}
<div class="flex justify-end mt-6 no-print">
    <a href="{{ route('sales.history.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
        &larr; Kembali ke Riwayat
    </a>
    <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Cetak Struk
    </button>
</div>
@endsection
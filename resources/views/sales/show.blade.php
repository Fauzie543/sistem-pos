@extends('layouts.app')

@section('header', 'Detail Penjualan: ' . $sale->invoice_number)

@push('styles')
<style>
@media print {
    /* --- Reset semua layout bawaan dashboard --- */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    /* Sembunyikan SEMUA elemen luar dari #print-area */
    body * {
        display: none !important;
    }

    /* Tampilkan hanya struk */
    #print-area, #print-area * {
        display: block !important;
        visibility: visible !important;
    }

    /* Pastikan struk tidak terpengaruh grid layout */
    #print-area {
        position: static !important;
        margin: 0 auto !important;
        width: 80mm !important; /* Ukuran nota thermal 80mm */
        padding: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    /* Hilangkan background Tailwind */
    .bg-gray-50, .bg-gray-100, .bg-gray-200, .bg-gray-300, .bg-white {
        background: #fff !important;
    }

    /* Atur teks & tabel */
    body, table {
        font-size: 12px !important;
        color: #000 !important;
        line-height: 1.3;
    }

    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    th, td {
        border: none !important;
        padding: 4px 6px !important;
        text-align: left;
    }

    /* Header */
    h2, h3, .text-lg, .text-xl {
        font-size: 14px !important;
        margin: 0 0 4px 0 !important;
    }

    /* Nonaktifkan tombol */
    .no-print {
        display: none !important;
    }

    /* Hilangkan margin default printer */
    @page {
        margin: 0;
    }
}
</style>
@endpush



@section('content')
<div id="print-area" class="bg-white p-6 rounded-md shadow-sm">
    {{-- Header Struk --}}
    <div class="flex justify-between items-start border-b pb-4 mb-4">
        <div>
            <h2 class="text-2xl font-bold">{{ $company->name ?? config('app.name', 'BengkelPOS') }}</h2>

            @if(!empty($company->address))
                <p class="text-sm text-gray-600">{{ $company->address }}</p>
            @endif

            @if(!empty($company->phone))
                <p class="text-sm text-gray-600">Telepon: {{ $company->phone }}</p>
            @endif

            @if(!empty($company->email))
                <p class="text-sm text-gray-600">Email: {{ $company->email }}</p>
            @endif
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
        <p>Terima kasih telah melakukan pembelian  kami.</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
    </div>

</div>

{{-- Tombol Aksi --}}
<div class="flex justify-end mt-6 no-print">
    <a href="{{ route('sales.history.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
        &larr; Kembali ke Riwayat
    </a>
    <a href="{{ route('sales.print', $sale->id) }}"  id="btnPrint" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
    Cetak Struk
    </a>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnPrint').addEventListener('click', function (e) {
    e.preventDefault(); // cegah pindah halaman

    const url = this.getAttribute('href');

    fetch(url)
        .then(res => res.text())
        .then(html => {
            // buat iframe tersembunyi untuk print
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);

            // tulis HTML print ke dalam iframe
            iframe.contentDocument.open();
            iframe.contentDocument.write(html);
            iframe.contentDocument.close();

            // tunggu sebentar agar CSS termuat, lalu print
            iframe.onload = function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();

                // hapus iframe setelah print
                setTimeout(() => iframe.remove(), 1000);
            };
        })
        .catch(err => console.error('Gagal memuat halaman print:', err));
});
</script>
@endpush
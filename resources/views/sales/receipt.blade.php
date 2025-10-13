<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; max-width: 300px; margin: 0 auto; }
        .center { text-align: center; }
        .item { display: flex; justify-content: space-between; }
        .total { display: flex; justify-content: space-between; font-weight: bold; border-top: 1px dashed black; padding-top: 5px; margin-top: 5px; }
        hr { border: none; border-top: 1px dashed black; }
    </style>
</head>
<body onload="window.print()">
    <div class="center">
        <h3>{{ config('app.name', 'BengkelPOS') }}</h3>
        <p>Jl. Teknik Kimia, Surabaya<br>Telp: (031) 123-4567</p>
    </div>
    <hr>
    <div>
        <p>No: {{ $sale->invoice_number }}<br>
        Kasir: {{ $sale->user->name }}<br>
        Tanggal: {{ $sale->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <hr>
    
    @foreach($sale->details as $detail)
    <div>
        <p>{{ $detail->product->name ?? $detail->service->name }}</p>
         @if(!empty($detail->note))
            <p style="font-style: italic; font-size: 11px; margin-top: -2px;">{{ $detail->note }}</p>
        @endif
        <div class="item">
            <span>{{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}</span>
            <span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach
    
    <div class="total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
    </div>

    <div class="center" style="margin-top: 20px;">
        <p>Terima Kasih!</p>
    </div>

    <script>
        // Setelah dialog print muncul, tutup window ini
        window.onafterprint = function() {
           window.close();
        };
    </script>
</body>
</html>
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
        <h3>{{ $company->name ?? 'Nama Perusahaan' }}</h3>
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

    @if(!empty($company->wifi_ssid) || !empty($company->wifi_password) || !empty($company->address) || !empty($company->instagram) || !empty($company->tiktok))
        <div class="center" style="margin-top: 15px;">
            @if(!empty($company->wifi_ssid) || !empty($company->wifi_password))
                @if(!empty($company->wifi_ssid))
                    <p style="margin: 0;">Username Wifi: {{ $company->wifi_ssid }}</p>
                @endif
                @if(!empty($company->wifi_password))
                    <p style="margin: 0;">Password Wifi: {{ $company->wifi_password }}</p>
                @endif
                <br>
            @endif

            @if(!empty($company->address) || !empty($company->phone))
                <p style="margin: 0;">
                    {{ $company->address ?? '' }}<br>
                    @if(!empty($company->phone)) Telp: {{ $company->phone }} @endif
                </p>
            @endif

            @if(!empty($company->instagram) || !empty($company->tiktok))
                <p style="margin-top: 4px;">
                    @if(!empty($company->instagram)) Instagram: {{ $company->instagram }}<br>@endif
                    @if(!empty($company->tiktok)) TikTok: {{ $company->tiktok }} @endif
                </p>
            @endif
        </div>
    @endif

    <div class="center" style="margin-top: 20px;">
        <p>Terima Kasih!</p>
        <p style="font-size: 10px; margin-top: 10px;">Powered by <strong>LemedoIT</strong></p>
    </div>

    <script>
        // Setelah dialog print muncul, tutup window ini
        window.onafterprint = function() {
           window.close();
        };
    </script>
</body>
</html>
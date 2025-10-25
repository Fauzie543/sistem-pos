<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $sale->invoice_number }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm 15mm;
        }

        h2, h3 {
            margin: 0;
            font-size: 18px;
        }

        p {
            margin: 2px 0;
            line-height: 1.4;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f9f9f9;
            text-align: left;
        }

        tfoot td {
            border-top: 2px solid #000;
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: #555;
        }

        /* Print only optimization */
        @media print {
            body {
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .container {
                padding: 10mm !important;
            }

            /* Hindari pemotongan tabel */
            table, tr, td, th, thead, tbody, tfoot {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- Header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2>{{ $company->name ?? 'Bengkel POS Default' }}</h2>
                <p>{{ $company->address ?? 'Surabaya, Indonesia' }}</p>
                @if(!empty($company->phone))
                    <p>Telepon: {{ $company->phone }}</p>
                @endif
            </div>
            <div class="text-right">
                <h3>INVOICE</h3>
                <p><strong>No:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Tanggal:</strong> {{ $sale->created_at->format('d F Y H:i') }}</p>
            </div>
        </div>

        <hr>

        {{-- Info Pelanggan & Kasir --}}
        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
            <div>
                <p><strong>Pelanggan:</strong> {{ $sale->customer->name }}</p>
                @if($sale->customer->phone_number)
                    <p>{{ $sale->customer->phone_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <p><strong>Kasir:</strong> {{ $sale->user->name }}</p>
            </div>
        </div>

        {{-- Tabel Item --}}
        <h3 class="mt-2">Rincian Item:</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $detail)
                <tr>
                    <td>{{ $detail->product->name ?? $detail->service->name }}</td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Grand Total</td>
                    <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Metode Pembayaran</td>
                    <td class="text-right">{{ Str::upper($sale->payment_method) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <p>Terima kasih telah melakukan pembelian di {{ $company->name ?? 'Bengkel POS Default' }}.</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
        </div>
    </div>
</body>
</html>

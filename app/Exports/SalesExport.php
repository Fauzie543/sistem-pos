<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
public function collection()
{
return Sale::with(['customer', 'user'])
->latest()
->get();
}

public function headings(): array
{
return [
'No Resi',
'Tanggal',
'Customer',
'Total',
'Kasir',
];
}

public function map($sale): array
{
return [
$sale->invoice_number,
$sale->created_at->format('d F Y H:i'),
$sale->customer->name ?? '-',
'Rp ' . number_format($sale->total_amount, 0, ',', '.'),
$sale->user->name ?? '-',
];
}
}
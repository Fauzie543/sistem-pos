<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $sales;
    protected $companyName;
    protected $totalAmount;

    public function __construct($sales, $companyName = 'KASLO POS')
    {
        $this->sales = $sales;
        $this->companyName = $companyName;
        $this->totalAmount = $sales->sum('total_amount');
    }

    public function collection()
    {
        return $this->sales;
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PENJUALAN ' . strtoupper($this->companyName)],
            ['Tanggal Cetak: ' . now()->format('d F Y H:i')],
            [],
            ['No Resi', 'Tanggal', 'Customer', 'Total', 'Kasir'],
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_number,
            $sale->created_at->format('d F Y H:i'),
            $sale->customer->name ?? '-',
            $sale->total_amount,
            $sale->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header utama
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Subheader (tanggal)
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Header kolom
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:E4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('DDEBF7');

        // Format kolom Total rata kanan
        $sheet->getStyle('D')->getAlignment()->setHorizontal('right');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 20,
            'C' => 25,
            'D' => 18,
            'E' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Format kolom D sebagai Rupiah
                foreach (range(5, $highestRow) as $row) {
                    $cell = 'D' . $row;
                    $value = $sheet->getCell($cell)->getValue();
                    if (is_numeric($value)) {
                        $sheet->setCellValue($cell, 'Rp ' . number_format($value, 0, ',', '.'));
                    }
                }

                // Tambahkan total di bawah
                $totalRow = $highestRow + 2;
                $sheet->setCellValue("C{$totalRow}", 'Total Pendapatan');
                $sheet->setCellValue("D{$totalRow}", 'Rp ' . number_format($this->totalAmount, 0, ',', '.'));

                // Style total baris
                $sheet->getStyle("C{$totalRow}:D{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("C{$totalRow}:D{$totalRow}")
                    ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Rata kanan total
                $sheet->getStyle("D{$totalRow}")->getAlignment()->setHorizontal('right');
            },
        ];
    }
}
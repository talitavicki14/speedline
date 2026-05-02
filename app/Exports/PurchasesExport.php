<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PurchasesExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize, 
    WithStyles,
    WithCustomStartCell,
    WithEvents,
    WithColumnFormatting
{
    protected $purchases;
    protected $startDate;
    protected $endDate;

    public function __construct(Collection $purchases, $startDate, $endDate)
    {
        $this->purchases = $purchases;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->purchases;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal',
            'Nama Barang',
            'Kategori',
            'Distributor',
            'Qty',
            'Harga Beli',
            'Total Harga',
        ];
    }

    public function map($p): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $p->purchase_date->format('d/m/Y'),
            $p->sparepart->name ?? '—',
            strtoupper($p->sparepart->type ?? '—'),
            $p->distributor->name ?? '—',
            $p->qty,
            (float) $p->purchase_price,
            (float) $p->total_price,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '"Rp "#,##0',
            'H' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'LAPORAN PEMBELIAN SPEEDLINE AUTOMOTIVE');
                $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:H2');
                $period = Carbon::parse($this->startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($this->endDate)->translatedFormat('d M Y');
                $sheet->setCellValue('A2', 'Periode: ' . $period);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setItalic(true);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A4:H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B5:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F5:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}

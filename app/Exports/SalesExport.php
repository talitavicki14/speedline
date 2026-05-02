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

class SalesExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize, 
    WithStyles,
    WithCustomStartCell,
    WithEvents,
    WithColumnFormatting
{
    protected $sparepartSales;
    protected $serviceSales;
    protected $startDate;
    protected $endDate;

    public function __construct(Collection $sparepartSales, Collection $serviceSales, $startDate, $endDate)
    {
        $this->sparepartSales = $sparepartSales;
        $this->serviceSales = $serviceSales;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $combined = collect();
        
        foreach ($this->sparepartSales as $sale) {
            $combined->push([
                'type' => 'SPAREPART',
                'date' => $sale->sale_date,
                'name' => $sale->sparepart->name ?? '—',
                'category' => $sale->sparepart->type ?? '—',
                'qty' => $sale->qty,
                'price' => $sale->price,
                'subtotal' => $sale->subtotal,
                'customer' => $sale->customer_name
            ]);
        }

        foreach ($this->serviceSales as $service) {
            $combined->push([
                'type' => 'LAYANAN',
                'date' => $service->sale_date,
                'name' => $service->service->service_name ?? '—',
                'category' => 'JASA',
                'qty' => 1,
                'price' => $service->price,
                'subtotal' => $service->price,
                'customer' => $service->customer_name
            ]);
        }

        return $combined->sortBy('date');
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tipe',
            'Tanggal',
            'Nama Produk/Layanan',
            'Kategori',
            'Qty',
            'Harga',
            'Subtotal',
            'Pelanggan',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row['type'],
            Carbon::parse($row['date'])->format('d/m/Y'),
            $row['name'],
            strtoupper($row['category']),
            $row['qty'],
            (float) $row['price'],
            (float) $row['subtotal'],
            $row['customer'],
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
                
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'LAPORAN PENJUALAN SPEEDLINE AUTOMOTIVE');
                $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:I2');
                $period = Carbon::parse($this->startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($this->endDate)->translatedFormat('d M Y');
                $sheet->setCellValue('A2', 'Periode: ' . $period);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setItalic(true);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A4:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B5:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F5:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}

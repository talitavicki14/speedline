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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FinanceExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize, 
    WithStyles,
    WithCustomStartCell,
    WithEvents,
    WithColumnFormatting
{
    protected $payments;
    protected $startDate;
    protected $endDate;

    public function __construct(Collection $payments, $startDate, $endDate)
    {
        $this->payments = $payments;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->payments;
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
            'No. Nota',
            'Pelanggan',
            'Kendaraan',
            'Metode',
            'Total Transaksi',
            'Jumlah Dibayar',
        ];
    }

    public function map($payment): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            Carbon::parse($payment->completed_at)->format('d/m/Y'),
            '#TRX-' . str_pad($payment->transaction_id, 4, '0', STR_PAD_LEFT),
            $payment->customer_name,
            $payment->vehicle_model ? $payment->vehicle_model . ' (' . $payment->license_plate . ')' : '—',
            strtoupper($payment->payment_method),
            (float) ($payment->transaction->grand_total ?? 0),
            (float) $payment->amount_paid,
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
                $sheet->setCellValue('A1', 'LAPORAN KEUANGAN SPEEDLINE AUTOMOTIVE');
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
                $sheet->getStyle('C5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F5:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}

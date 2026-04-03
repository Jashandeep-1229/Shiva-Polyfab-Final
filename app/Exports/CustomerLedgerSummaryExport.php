<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class CustomerLedgerSummaryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $customers;
    protected $from_date;
    protected $to_date;

    public function __construct($customers, $from_date = null, $to_date = null)
    {
        $this->customers = $customers;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function collection()
    {
        return $this->customers;
    }

    public function title(): string
    {
        return 'Customer Ledger Summary';
    }

    public function headings(): array
    {
        return [
            '#',
            'Customer Name',
            'Code',
            'Phone',
            'Type',
            'Sale Executive',
            'Debit Balance',
            'Credit Balance'
        ];
    }

    public function map($customer): array
    {
        static $index = 1;
        $balance = round($customer->balance, 2);
        
        return [
            $index++,
            $customer->name,
            $customer->code,
            $customer->phone_no,
            $customer->role,
            $customer->sale_executive->name ?? 'N/A',
            $balance > 0 ? $balance : 0,
            $balance < 0 ? abs($balance) : 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('4466F2');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFF');
        
        // Add borders to everything
        $lastRow = count($this->customers) + 1;
        $sheet->getStyle("A1:H{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Format columns G and H as currency/number
        $sheet->getStyle("G2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        return [];
    }
}

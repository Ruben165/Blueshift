<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarcodeAddExport implements FromCollection, WithHeadings, WithStyles
{
    protected $partnerItem;

    public function __construct(Collection $partnerItem)
    {
        $this->partnerItem = $partnerItem;
    }

    public function collection()
    {
        return collect([
            [
                'ID DATABASE' => $this->partnerItem['barcode_id'],
                'NAMA OBAT' => $this->partnerItem['name'],
                'QTY' => $this->partnerItem['stock_qty'],
                'EXP' => $this->partnerItem['exp']
            ]
        ]
        );
    }
    
    public function headings(): array
    {
        return [
            'ID DATABASE',
            'NAMA OBAT',
            'QTY',
            'EXP'
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
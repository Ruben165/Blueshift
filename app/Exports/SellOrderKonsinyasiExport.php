<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SellOrderKonsinyasiExport implements FromCollection, WithHeadings, WithStyles
{
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return collect($this->items)->map(function ($item, $index) {
            if($item->pivot->quantity != 0){
                $dateExp = Carbon::createFromFormat('Y-m-d', $item->exp_date);
    
                return [
                    (int) $index + 1 => [
                        'No' => (int) $index + 1,
                        'Product Name Description' => strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')'),
                        'Batch-Exp' => $item->batch . '-' . $dateExp->format('m/y'),
                        'Qty' => $item->pivot->quantity,
                        'Unit Price' => $item->pivot->total / $item->pivot->quantity,
                        'Total' => $item->pivot->quantity * $item->pivot->total / $item->pivot->quantity
                    ]
                ];
            }
            else{
                return [];
            }
        });
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Product Name Description',
            'Batch-Exp',
            'Qty',
            'Unit Price',
            'Total'
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
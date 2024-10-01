<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SellOrderExport implements FromCollection, WithHeadings, WithStyles
{
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return collect($this->items)->map(function ($item, $index) {
            $dateExp = Carbon::createFromFormat('Y-m-d', $item->exp_date);

            return [
                (int) $index + 1 => [
                    'ID SKU' => $item->item->sku,
                    'ID Database' => $item->barcode_id,
                    'Nama Barang' => strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')'),
                    'Satuan' => strtoupper($item->item->unit),
                    'Batch-Exp' => $item->batch . '-' . $dateExp->format('m/y'),
                    'Qty' => $item->pivot->quantity,
                    'Gol' => strtoupper($item->item->type->name)
                ]
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'ID SKU',
            'ID Database',
            'Nama Barang',
            'Satuan',
            'Batch-Exp',
            'Qty',
            'Gol'
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
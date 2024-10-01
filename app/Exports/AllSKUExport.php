<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllSKUExport implements FromCollection, WithHeadings, WithStyles
{
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return collect($this->items)->map(function ($item, $index) {
            return [
                (int) $index + 1 => [
                    'ID' => $item->sku,
                    'Nama Item' => $item->name .' ('.$item->content.') ('.$item->packaging.').',
                    'Golongan Item' => $item->type->name,
                    'Satuan' => $item->unit,
                    'Pabrik' => $item->manufacturer,
                    'Supplier' => $item->supplier->name,
                    'Harga' => $item->price,
                ]
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Nama Item',
            'Golongan Item',
            'Satuan',
            'Pabrik',
            'Supplier',
            'Harga',
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
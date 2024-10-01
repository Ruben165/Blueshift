<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllHQStockExport implements FromCollection, WithHeadings, WithStyles
{
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return collect($this->items)->map(function ($item, $index) {
            $dateExp = Carbon::parse($item->exp_date);
            $dateExp = $dateExp->format('d/m/Y');

            $stockOnProcess = DB::table('sell_order_details AS sod')
                                ->join('sell_orders AS so', 'so.id', '=', 'sod.sell_order_id')
                                ->where('sod.item_id', $item->id)
                                ->where('sod.quantity', '>', 0)
                                ->where('so.status_id', 1)
                                ->where('so.source_partner_id', $item->partner->id)
                                ->sum('sod.quantity');

            return [
                (int) $index + 1 => [
                    'ID PRODUK' => $item->item->sku,
                    'ID DATABASE' => $item->barcode_id,
                    'PABRIK' => $item->item->manufacturer,
                    'NAMA OBAT' => $item->item->name,
                    'SATUAN' => $item->item->unit,
                    'QTY' => $item->stock_qty,
                    'QTY ON PROCESS' => $stockOnProcess,
                    'GOL' => $item->item->type->name,
                    'EXP' => $dateExp,
                    'NO BATCH' => substr($item->batch, 0, 1) == '0' ? $item->batch : $item->batch,
                    'KODE RAK' => $item->shelf->name,
                    'HARGA DISKON' => $item->getRawOriginal('discount_price')
                ]
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'ID PRODUK',
            'ID DATABASE',
            'PABRIK',
            'NAMA OBAT',
            'SATUAN',
            'QTY',
            'QTY ON PROCESS',
            'GOL',
            'EXP',
            'NO BATCH',
            'KODE RAK',
            'HARGA DISKON'
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
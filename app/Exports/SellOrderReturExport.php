<?php
namespace App\Exports;

use App\Models\PartnerItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SellOrderReturExport implements FromCollection, WithHeadings, WithStyles
{
    protected $sell;

    public function __construct(Model $sell)
    {
        $this->sell = $sell;
    }

    public function collection()
    {
        return collect($this->sell->partnerItems)->map(function ($item, $index) {
            $itemSelected = PartnerItem::where('partner_id', $this->sell->source_partner_id)
                                        ->where('item_id', $item->item_id)
                                        ->where('barcode_id', $item->barcode_id)
                                        ->first();
            $stok = $itemSelected->stock_qty;

            return [
                (int) $index + 1 => [
                    'ID Database' => $item->barcode_id,
                    'Nama Barang' => strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')'),
                    'Satuan' => strtoupper($item->item->unit),
                    'Stok' => $stok,
                    'Stok Retur' => $item->pivot->quantity,
                    'Stok Sisa' => $stok - $item->pivot->quantity
                ]
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'ID Database',
            'Nama Barang',
            'Satuan',
            'Qty',
            'Stok Retur',
            'Stok Sisa',
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
<?php
namespace App\Exports;

use App\Models\PartnerItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllBarcodeExport implements FromCollection, WithHeadings, WithStyles
{
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return collect($this->items)->map(function ($item, $index) {
            $partnerItem = PartnerItem::find($item->pivot->partner_item_id);
            $dateExp = Carbon::createFromFormat('Y-m-d', $partnerItem->exp_date);
            return [
                (int) $index + 1 => [
                    'ID DATABASE' => $partnerItem->barcode_id,
                    'NAMA OBAT' => strtoupper($partnerItem->item->name . ' ' . getBerat($partnerItem->item->packaging) . ' - ' . $partnerItem->item->supplier->name),
                    'QTY' => $item->pivot->qty_came,
                    'EXP' => $partnerItem->batch . '/' . $dateExp->format('m Y') . ' (' .(  @$partnerItem->shelf->name ?? '-' ). ')'
                ]
            ];
        });
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
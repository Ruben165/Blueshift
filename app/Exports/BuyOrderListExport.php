<?php
namespace App\Exports;

use App\Models\PartnerItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BuyOrderListExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        $list = new Collection();
        foreach($this->items as $item){
            if($item->items->count()){
                $faktur = json_decode($item->faktur);

                foreach($item->items as $index => $sku){
                    $partnerItem = PartnerItem::find($sku->pivot->partner_item_id);
                    $resultFaktur = null;

                    if($faktur && $sku->pivot->faktur){
                        $fakturKey = array_search($sku->pivot->faktur, array_column($faktur, 'id'));
                        $resultFaktur = explode(' | ', $faktur[$fakturKey]->faktur);
                    }

                    $data = new Collection([[
                        'Nomor SP' => $index == 0 ? $item->SP_no : '',
                        'PBF' => $index == 0 ? $item->supplier->name : '',
                        'Status' => $index == 0 ? $item->status->name : '',
                        'Tanggal SP' => $index == 0 && $item->SP_date ? Carbon::parse($item->SP_date)->format('d/m/Y') : '',
                        'Tanggal Approve' => $index == 0 && $item->approve_date ? Carbon::parse($item->approve_date)->format('d/m/Y') : '',
                        'Tanggal Kirim' => $index == 0 && $item->send_date ? Carbon::parse($item->send_date)->format('d/m/Y') : '',
                        'Tanggal Terima' => $index == 0 && $item->receive_date ? Carbon::parse($item->receive_date)->format('d/m/Y') : '',
                        'ID Barcode' => $partnerItem ? $partnerItem->barcode_id : '-',
                        'Nama Item' => $sku->name, 
                        'ID SKU' => $sku->sku,
                        'ID CR/PO/BR' => $sku->pivot->id_CRPOBR ?? '-',
                        'Klinik' => $sku->pivot->clinic ?? '-',
                        'Kuantitas Datang' => $sku->pivot->qty_came,
                        'Nomor Faktur' => $resultFaktur ? $resultFaktur[0] : '-',
                        'Tanggal Faktur' => $resultFaktur ? $resultFaktur[1] : '-',
                        'Batch' => $partnerItem ? $partnerItem->batch : '-',
                        'ED' => $partnerItem ? Carbon::parse($partnerItem->exp_date)->format('m/Y') : '-',
                        'Rak' => $partnerItem && $partnerItem->shelf_id ? $partnerItem->shelf->name : '-',
                        'HNA Satuan' => $sku->pivot->HNA_each,
                        'Diskon' => $sku->pivot->discount . '%',
                        'Harga Beli' => $sku->pivot->buy_price,
                        'Jumlah' => $sku->pivot->amount,
                        'Note' => $sku->pivot->note ?? '-'
                    ]]);
                    $list = $list->merge($data);
                }
            }
        }
        return $list;
    }
    
    public function headings(): array
    {
        return [
            'Nomor SP',
            'PBF',
            'Status',
            'Tanggal SP',
            'Tanggal Approve',
            'Tanggal Kirim',
            'Tanggal Terima',
            'ID Barcode',
            'Nama Item',
            'ID SKU',
            'ID CR/PO/BR',
            'Klinik',
            'Kuantitas Datang',
            'Nomor Faktur',
            'Tanggal Faktur',
            'Batch',
            'ED',
            'Rak',
            'HNA Satuan',
            'Diskon',
            'Harga Beli',
            'Jumlah',
            'Note'
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:W1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
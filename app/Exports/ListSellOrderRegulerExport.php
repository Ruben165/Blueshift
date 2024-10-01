<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListSellOrderRegulerExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
            if($item->partnerItems->count()){
                foreach($item->partnerItems as $partnerItem){
                    $data = new Collection([[
                        'ID Pengiriman' => $item->document_number ?? '-',
                        'ID Permintaan' => $item->id_request ?? '-',
                        'Klinik Tujuan' => $item->destinationPartner->name,
                        'Status'              => $item->status->name,
                        'Tanggal Pemesanan'     => Carbon::parse($item->created_at)->format('d/m/Y'),
                        'Tanggal Pengiriman'   => $item->delivered_at ? Carbon::parse($item->delivered_at)->format('d/m/Y') : '',
                        'Barcode ID'                   => $partnerItem->barcode_id,
                        'Nama Item'                     => $partnerItem->item->name, 
                        'ID SKU'                           => $partnerItem->item->sku,
                        'Kuantitas Request'     => $partnerItem->pivot->quantity,
                        'Batch-Exp'                    => $partnerItem->batch.'-'.Carbon::parse($partnerItem->exp_date)->format('m/y'),            
                        'Harga Barang Saat Pemesanan'   => $partnerItem->pivot->total / $partnerItem->pivot->quantity,
                        'Jumlah'                          => $partnerItem->pivot->total
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
            'ID Pengiriman',
            'ID Permintaan',
            'Klinik Tujuan',
            'Status',
            'Tanggal Pemesanan',
            'Tanggal Pengiriman',
            'Barcode ID',
            'Nama Item', 
            'ID SKU',
            'Kuantitas Request',
            'Batch-Exp',
            'Harga Barang Saat Pemesanan',
            'Jumlah'
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListSellOrderTransferExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $items;

    public function __construct(Collection $items)
    {
        //UNUSED
        $this->items = $items;
    }

    public function collection()
    {
        $list = new Collection();
        foreach($this->items as $item){
            if($item->partnerItems->count()){
                foreach($item->partnerItems as $partnerItem){
                    $data = new Collection([[
                        'Tipe Pemesanan' => 'Transfer',
                        'Klinik Sumber' => $item->sourcePartner->name,
                        'Klinik Tujuan' => $item->destinationPartner->name,
                        'Status'              => $item->status->name,
                        'Nomor Document'           => $item->document_number,
                        'Tanggal Pemesanan'     => Carbon::parse($item->created_at)->format('d/m/Y'),
                        'Tanggal Pengiriman'   => Carbon::parse($item->returned_at ?? $item->delivered_at)->format('d/m/Y'),
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
            'Tipe Pemesanan',
            'Klinik Sumber',
            'Klinik Tujuan',
            'Status',
            'Nomor Document',
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
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
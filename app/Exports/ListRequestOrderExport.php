<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListRequestOrderExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
                foreach($item->items as $sku){
                    $data = new Collection([[
                        'ID Permintaan' => $item->id_request ?? '-',
                        'ID Pengiriman' => $sku->pivot->sender_id ?? '-',
                        'Nama Klinik    ' => $item->partner->name,
                        'Nama Sales'       => $item->partner->sales_name,
                        'Tanggal Permintaan'   => $sku->pivot->request_date ? Carbon::parse($sku->pivot->request_date)->format('d/m/Y') : '',
                        'Tanggal Pengiriman'   => $sku->pivot->deliver_date ? Carbon::parse($sku->pivot->deliver_date)->format('d/m/Y') : '',
                        'Status'                          => $item->status->name,
                        'Nama Item'                     => $sku->name, 
                        'ID SKU'                           => $sku->sku,
                        'Kuantitas Request'     => $sku->pivot->quantity,
                        'Kuantitas Kirim'     => $sku->pivot->quantity_send,
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
            'ID Permintaan',
            'ID Pengiriman',
            'Nama Klinik',
            'Nama Sales',
            'Tanggal Permintaan',
            'Tanggal Pengiriman',
            'Status',
            'Nama Item', 
            'ID SKU',
            'Kuantitas Request',
            'Kuantitas Kirim'
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
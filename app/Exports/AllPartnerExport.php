<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllPartnerExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
                    'Nama Mitra'            => $item->name,
                    'ID Klinik'              => $item->clinic_id,
                    'Email'                     => $item->email,
                    'No Telephone'        => $item->phone,
                    'Tipe Konsinyasi'  => $item->allow_consign ? 'Konsinyasi' : 'Reguler',
                    'Batch'                     => $item->groups->count() > 0 ? $item->groups->pluck('name')->implode(', ') : '-', 
                    'Wilayah'                 => $item->zones->count() > 0 ? $item->zones->pluck('name')->implode(', ') : '-',
                    'Alamat Klinik'      => $item->address,
                    'Nama Sales'            => $item->sales_name
                ]
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'Nama Mitra',
            'ID Klinik',
            'Email',
            'No Telephone',
            'Tipe Konsinyasi',
            'Batch', 
            'Wilayah',
            'Alamat Klinik',
            'Nama Sales'
        ];
    }

    public function styles(WorkSheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
?>
<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BuyOrderExport implements FromCollection, WithHeadings, WithStyles
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
                    'No' => (int) $index + 1,
                    'Nama Obat' => $item->name . ' (' . $item->supplier->name . ')',
                    'Bentuk Sediaan' => $this->getBentukSediaan($item->packaging),
                    'Kekuatan/Dosis' => $item->content,
                    'Qty (angka dan terbilang)' => $item->pivot->quantity . ' ( ' . $this->getSpokenNumber($item->pivot->quantity) . ' )',
                    'Satuan' => $item->unit,
                    'Harga Total' => 'Rp'.number_format($item->pivot->total, 2)
                ]
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Nama Obat',
            'Bentuk Sediaan',
            'Kekuatan/Dosis',
            'Qty (angka dan terbilang)',
            'Satuan',
            'Harga Total'
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

    public function getSpokenNumber($number)
    {
        $ones = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($number < 12) {
            return $ones[$number];
        } elseif ($number < 20) {
            return $ones[$number - 10] . ' Belas';
        } elseif ($number < 100) {
            return $ones[floor($number / 10)] . ' Puluh ' . $ones[$number % 10];
        } elseif ($number < 200) {
            return 'Seratus ' . getSpokenNumber($number - 100);
        } elseif ($number < 1000) {
            return $ones[floor($number / 100)] . ' Ratus ' . getSpokenNumber($number % 100);
        } else {
            return 'Number out of range';
        }
    }

    public function getBentukSediaan($input)
    {
        if(!isset(explode(' ', $input)[1]) || !isset(explode(',', explode(' ', $input)[1])[0])){
            return '-';
        }

        $parts = explode(',', explode(' ', $input)[1]);
        if (isset($parts[0])) {
            $term = trim($parts[0]);
            return $term;
        }
        return '';
    }
}
?>
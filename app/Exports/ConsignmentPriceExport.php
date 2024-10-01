<?php
namespace App\Exports;

use App\Models\PartnerItem;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ConsignmentPriceExport implements FromView, WithStyles, WithColumnWidths
{
    protected $prices;
    protected $totalSum;
    protected $sellOrder;
    protected $isAll;
    protected $priceType;

    public function __construct(Collection $priceList, array $totalType, Model $sellOrder, bool $isAll, array $priceType)
    {
        $this->prices = $priceList;
        $this->totalSum = $totalType;
        $this->sellOrder = $sellOrder;
        $this->isAll = $isAll;
        $this->priceType = $priceType;
    }

    public function view(): View
    {
        $prices = $this->prices;
        $totalSum = $this->totalSum;
        $sellOrder = $this->sellOrder;
        $isAll = $this->isAll;
        $priceType = $this->priceType;

        $typeExport = 'excel';
        return view('sell.konsinyasi.export', compact(['prices', 'totalSum', 'sellOrder', 'isAll', 'priceType', 'typeExport']));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E'. 3 + (count($this->prices['Umum']) == 0 ? 0 : count($this->prices['Umum']) + 1) + (count($this->prices['Khusus']) == 0 ? 0 : count($this->prices['Khusus']) + 1) + (count($this->prices['Prekursor']) == 0 ? 0 : count($this->prices['Prekursor']) + 1) + (count($this->prices['Alkes']) == 0 ? 0 : count($this->prices['Alkes']) + 1) + (count($this->prices['Obat Obat Tertentu']) == 0 ? 0 : count($this->prices['Obat Obat Tertentu']) + 1))
                ->getFont()
                ->setName('Arial');
        $sheet->getStyle('A1:E'. 3 + (count($this->prices['Umum']) == 0 ? 0 : count($this->prices['Umum']) + 1) + (count($this->prices['Khusus']) == 0 ? 0 : count($this->prices['Khusus']) + 1) + (count($this->prices['Prekursor']) == 0 ? 0 : count($this->prices['Prekursor']) + 1) + (count($this->prices['Alkes']) == 0 ? 0 : count($this->prices['Alkes']) + 1) + (count($this->prices['Obat Obat Tertentu']) == 0 ? 0 : count($this->prices['Obat Obat Tertentu']) + 1))
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(Alignment::VERTICAL_CENTER);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 5,
            'C' => 8,
            'D' => 10,
            'E' => 15
        ];
    }
}
?>
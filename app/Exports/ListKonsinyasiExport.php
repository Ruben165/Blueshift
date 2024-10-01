<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ListKonsinyasiExport implements FromView, WithStyles, WithColumnWidths
{
    protected $partnerItems;
    protected $formated_date;
    protected $partner;
    protected $isHasilSO;

    public function __construct(Collection $partnerItems, $formated_date, $partner, $isHasilSO = false)
    {
        $this->partnerItems = $partnerItems;
        $this->formated_date = $formated_date;
        $this->partner = $partner;
        $this->isHasilSO = $isHasilSO;
    }

    public function view(): View
    {
        $partnerItems = $this->partnerItems;
        $formated_date = $this->formated_date;
        $partner = $this->partner;
        $isHasilSO = $this->isHasilSO;
        // dd($isHasilSO);
        // dd($partnerItems);

        return view('sell.listKonsinyasiExcel', compact(['partnerItems', 'formated_date', 'partner', 'isHasilSO']));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J' . 1 + count($this->partnerItems))
                ->getFont()
                ->setName('Tahoma');
        $sheet->getStyle('A1:J' . 1 + count($this->partnerItems))
                ->getAlignment()
                ->setWrapText(true)
                ->setVertical(Alignment::VERTICAL_CENTER);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 50,
            'C' => 12,
            'D' => 18,
            'E' => 15,
            'F' => 8,
            'G' => 8,
            'H' => 8,
            'I' => 8,
            'J' => 8
        ];
    }
}
?>
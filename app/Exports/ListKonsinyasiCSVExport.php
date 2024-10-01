<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ListKonsinyasiCSVExport implements FromView
{
    protected $partnerItems;
    protected $partner;

    public function __construct(Collection $partnerItems, $partner)
    {
        $this->partnerItems = $partnerItems;
        $this->partner = $partner;
    }

    public function view(): View
    {
        $partnerItems = $this->partnerItems;
        $partner = $this->partner;

        return view('sell.listKonsinyasiCSV', compact(['partnerItems', 'partner']));
    }
}
?>
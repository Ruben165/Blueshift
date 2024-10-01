<?php
namespace App\Exports;

use App\Models\PartnerItem;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ListSisaSOCSVExport implements FromView
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

        return view('sell.listKonsinyasiSOCSV', compact(['partnerItems', 'partner']));
    }
}
?>
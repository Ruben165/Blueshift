<?php
namespace App\Exports;

use App\Models\PartnerItem;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ConsignmentRequestExport implements FromView, WithStyles, WithColumnWidths
{
    protected $consignmentRequest;
    protected $itemCount;

    public function __construct(Model $consignmentRequest)
    {
        $this->consignmentRequest = $consignmentRequest;
        $this->itemCount = count($consignmentRequest->items);
    }

    public function view(): View
    {
        $consignmentRequest = $this->consignmentRequest;
        $typeExport = 'excel';
        return view('sell.permintaan.export', compact(['consignmentRequest', 'typeExport']));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J'. 6 + $this->itemCount)->getFont()->setName('Arial');
        $sheet->getStyle('A1:J'. 6 + $this->itemCount)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 50,
            'D' => 20,
            'E' => 10,
            'F' => 8,
            'G' => 8,
            'H' => 10,
            'I' => 10,
            'J' => 10
        ];
    }
}
?>
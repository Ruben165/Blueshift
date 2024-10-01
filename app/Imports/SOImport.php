<?php

namespace App\Imports;

use App\Models\SOImport as ModelsSOImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SOImport implements ToModel, WithHeadingRow
{
    private $partnerId;

    public function __construct($partnerId)
    {
        $this->partnerId = $partnerId;
    }

    public function model(array $row)
    {
        if($row['barcode'] != null){
            return new ModelsSOImport([
                'barcode_id' => str_pad($row['barcode'], 12, '0', STR_PAD_LEFT),
                'quantity' => $row['quantity'],
                'partner_id' => $this->partnerId
            ]);
        }
    }
}

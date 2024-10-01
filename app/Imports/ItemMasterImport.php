<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\Sheets\ItemMasterSheet;

class ItemMasterImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new ItemMasterSheet()
        ];
    }
}

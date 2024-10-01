<?php

namespace App\Imports\Sheets;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\Type;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ItemMasterSheet implements ToModel, WithUpserts, WithHeadingRow
{
    public function uniqueBy()
    {
        return ['id', 'sku'];
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $supplier = Supplier::where('name', 'LIKE', '%'.$row['supplier'].'%')->first();
        $type = Type::where('name', 'LIKE', '%'.$row['gol'].'%')->first();
        $item = Item::updateOrCreate([
                'sku'          => $row['id_item'],
        ], [
            'name'         => $row['nama_item'],
            'content'      => $row['kandungan'],
            'packaging'    => $row['kemasan'] .', '. $row['sediaan'],
            'supplier_id' => $supplier->id,
            'manufacturer'  => $row['pabrik'],
            'unit'         => $row['satuan'],
            'type_id'      => $type->id,
            'price'        => $row['harga'] ? $row['harga'] : '0'
        ]);
        return $item;
    }
}

<?php

namespace App\Imports;

use App\Imports\Sheets\ItemStockSheet;
use App\Models\Item;
use App\Models\PartnerItem;
use App\Models\Shelf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemStockImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $rows = $rows->filter(function ($item){
            return $item['id_produk'] !== null;
        });
        foreach($rows as $row){
            $rak = Shelf::where('name', 'LIKE', $row['kode_rak'])->first();

            $rak = $rak ? $rak->id : $this->createNewShelf(strtoupper($row['kode_rak']));

            $item = Item::where('sku', trim($row['id_produk']))->first();
            
            if(!$item){
                throw new \Exception('Produk tidak terdaftar pada master SKU, harap tambahkan terlebih dahulu!');
            }
            
            if(strlen($row['exp']) == 5){
                $convertedExp = Carbon::createFromFormat('Y-m-d', date('Y-m-d', (($row['exp'] - 25569) * 86400)))->format('Y-m-d');
            }
            else{
                $convertedExp = Carbon::createFromFormat('d/m/Y', $row['exp'])->format('Y-m-d');
            }

            if(isset($row['id_database']) && trim($row['id_database']) != null){
                $barcodeId = trim($row['id_database']);
            }
            else{
                $barcodeId = $this->getBarcodeAndUpdateStock(trim($row['id_produk']), $convertedExp, $row['no_batch'], trim($row['qty']), $rak);
            }

            if((isset($row['id_database']) && $row['id_database'] != null)){
                $item->partners()->attach(1, [
                    'shelf_id' => $rak,
                    'barcode_id' => $barcodeId,
                    'batch' => $row['no_batch'],
                    'exp_date' => $convertedExp,
                    'stock_qty' => $row['qty'],
                    'discount_price' => $row['harga_diskon'],
                    'is_consigned' => 0
                ]);
            }
            else if($barcodeId['createNew'] == true){
                $item->partners()->attach(1, [
                    'shelf_id' => $rak,
                    'barcode_id' => $barcodeId['id'],
                    'batch' => $row['no_batch'],
                    'exp_date' => $convertedExp,
                    'stock_qty' => $row['qty'],
                    'discount_price' => $row['harga_diskon'],
                    'is_consigned' => 0
                ]);
            }
        }
    }

    public function createNewShelf($kodeRak){
        $newShelf = new Shelf;
        $newShelf->name = $kodeRak;
        $newShelf->save();

        return $newShelf->id;
    }

    public function getBarcodeAndUpdateStock($sku, $exp, $batch, $qty, $rak, $isFailed = false){
        $theItem = Item::where('sku', $sku)->first();
        $items = PartnerItem::where('partner_id', 1)->where('item_id', $theItem->id)->orderBy('created_at', 'DESC')->get();

        $sameSkuExpBatchExists = false;
        $sameSkuExpExists = false;
        $sameBarcode = null;
    
        $maxBatchNo = 0;
        $returnedValue = null;

        $newSku = strlen($sku) < 5 ? str_pad($sku, 5, '0', STR_PAD_LEFT) : $sku;

        $dateParts = explode('-', $exp);
        $newDate = $dateParts[1].substr($dateParts[0], -2);

        if($items->count() == 0){
            $returnedValue = '001'.$newSku.$newDate;
        }
        else{
            foreach ($items as $partner) {
                if(explode('-', $partner->exp_date)[0] . '-' . explode('-', $partner->exp_date)[1] == explode('-', $exp)[0] . '-' . explode('-', $exp)[1]) {
                    if($partner->batch == $batch && $partner->shelf_id == $rak) {
                        $partner->stock_qty = $partner->stock_qty + $qty;
                        $partner->save();
                
                        return [
                            'id' => $partner->barcode_id,
                            'createNew' => false
                        ];
                    }
                    else if($partner->batch == $batch && $partner->shelf_id != $rak){
                        $sameSkuExpBatchExists = true;
                        $sameBarcode = $partner->barcode_id;
                    }
                    else {
                        $sameSkuExpExists = true;
                
                        $batchNo = (int) substr($partner->barcode_id, 0, 3);
                
                        $maxBatchNo = max($maxBatchNo, $batchNo);
                    }
                }
                elseif(!$sameSkuExpExists && $returnedValue == null) {
                    $returnedValue = '001'.$newSku.$newDate;
                }
            }
    
            if($sameSkuExpBatchExists){
                return [
                    'id' => $sameBarcode,
                    'createNew' => true
                ];
            }
            else if($sameSkuExpExists){
                if(strlen($maxBatchNo) == 1)
                    $maxBatchNo = sprintf("%03d", $maxBatchNo+1);
                else if(strlen($maxBatchNo) == 2)
                    $maxBatchNo = sprintf("%02d", $maxBatchNo+1);
                
                $returnedValue = $maxBatchNo.$newSku.$newDate;
            }
        }

        return [
            'id' => $returnedValue,
            'createNew' => true
        ];
    }
}

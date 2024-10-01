<?php

namespace App\Imports;

use App\Models\LogError;
use App\Models\PBFImport as ModelsPBFImport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PBFItemsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try{
            if(isset($row['sku'])){
                $fakturExist = isset($row['no_faktur']) && isset($row['tanggal_faktur']);

                if($fakturExist){
                    if(strlen($row['tanggal_faktur']) == 5){
                        $convertedDate = Carbon::createFromFormat('Y-m-d', date('Y-m-d', (($row['tanggal_faktur'] - 25569) * 86400)))->format('Y-m-d');
                    }
                    else{
                        $convertedDate = Carbon::createFromFormat('d/m/Y', $row['tanggal_faktur'])->format('Y-m-d');
                    }
                }

                $data = new ModelsPBFImport([
                    'sku' => $row['sku'],
                    'qtyRequest' => $row['qty_pesan'],
                    'idCRPOBR' => $row['id_crpobr'] ?? null,
                    'clinic' => $row['klinik'] ?? null,
                    'qtyCame' => $row['qty_datang'] ?? 0,
                    'faktur' => $fakturExist ? $row['no_faktur'] . ' | ' . $convertedDate : null,
                    'batch' => $row['batch'] ?? null,
                    'expired' => $row['ed'] ?? '',
                    'shelf' => $row['rak'] ?? null,
                    'HNAEach' => $row['hna_satuan'] ?? 0,
                    'discount' => $row['diskon'] ?? 0,
                    'note' => $row['note'] ?? null
                ]);

                return $data;
            }
        } catch(\Exception $e){
            LogError::insertLogError($e->getMessage());

            return;
        }
    }
}

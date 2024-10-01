<?php

namespace App\Http\Controllers;

use App\Exports\PriceConvertACMExport;
use App\Exports\PriceConvertBPLExport;
use App\Exports\StockConvertACMExport;
use App\Exports\StockConvertBPLExport;
use App\Imports\PriceStockConvertImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OtherController extends Controller
{
    public function indexPrice(){
        return view('other.price.index');
    }

    public function storePrice(Request $request){
        $file = $request->file('file');
        $data = Excel::toArray(new PriceStockConvertImport, $file)[0];
        array_shift($data);

        if($request->type == 'BPL'){
            $catalogueDivider = $request->catalogueDivider;

            // Row 0 -> No. (Remove jadi Kode Barang)
            // Row 1 -> Kode Barang (Remove jadi Pabrik)
            // Row 2 -> Pabrik (Remove jadi Nama Barang)
            // Row 3 -> Nama Barang (Remove jadi Satuan)
            // Row 4 -> Satuan (Remove jadi Harga)
            // Row 5 -> Harga (Remove jadi Harga Dikali)
            // Row 5 -> Harga Dikali
            // Row 6 -> Discount
            // Row 7 -> Discounted Price
            // Row 8 -> Catalogue Price
            // Row 9 -> Percentage Difference
            // Row 10 -> Difference Price
            // Row 11 -> Harga Jual
            foreach($data as &$row){
                array_shift($row); // Hilangin column NO
                switch($row[3]){
                    case "BTL":
                        $row[5] = (float) $row[4] * 4;
                        break;
                    case "FLS":
                        $row[5] = (float) $row[4] * 4;
                        break;
                    case "TUBE":
                        $row[5] = (float) $row[4] * 10;
                        break;
                    case "TUB":
                        $row[5] = (float) $row[4] * 10;
                        break;
                    default:
                        $row[5] = (float) $row[4];
                }

                $row[6] = 20.00;
                $row[7] = round($row[5] - ($row[5] * $row[6] / 100), 2);
                if(!is_numeric($row[4])){
                    $row[5] = $row[6] = $row[7] = $row[8] = $row[9] = $row[10] = $row[11] = null;
                }
                else{
                    $row[8] = round($row[7]/$catalogueDivider, 2);
                    $row[9] = round((($row[8] - $row[7])/$row[7])*100, 2);
                    $row[10] = $row[8] - $row[7];
                    $row[11] = ceil($row[8] / 100) * 100;
                }

                unset($row[1], $row[3]);
                $row = array_values($row);
            }
            
            return Excel::download(new PriceConvertBPLExport($data), 'PRICE-BPL-CONVERTED-'.Carbon::now().'.xlsx');
        }
        else{
            // Row 0 -> Nama Barang
            // Row 1 -> Disc
            // Row 2 -> HNA
            // Row 3 -> HNA Dikali
            // Row 4 -> Discounted Price
            // Row 5 -> Catalogue Price
            // Row 6 -> Percentage Difference
            // Row 7 -> Difference Price
            // Row 8 -> Harga Jual

            foreach($data as $key => &$row){
                $row[3] = $row[2];
                $row[4] = round((float) $row[3] - ((float) $row[3] * (float) $row[1] / 100), 2);
                
                if($row[1] >= 50){
                    $row[6] = 12;
                }
                else if($row[1] >= 20){
                    $row[6] = 11;
                }
                else if($row[1] >= 15){
                    $row[6] = 10;
                }
                else if($row[1] >= 12){
                    $row[6] = 9;
                }
                else{
                    unset($data[$key]);
                    continue;
                }

                $row[5] = round($row[4] + ($row[4] * $row[6] / 100), 2);
                $row[7] = $row[5] - $row[4];
                $row[8] = ceil($row[5] / 100) * 100;

                // Reorder Array
                $row = array_replace($row, [
                    5 => $row[6],
                    6 => $row[5] 
                ]);
            }
            
            $data = array_values($data);
            
            return Excel::download(new PriceConvertACMExport($data), 'PRICE-ACM-CONVERTED-'.Carbon::now().'.xlsx');
        }
    }

    public function indexStock(){
        return view('other.stock.index');
    }

    public function storeStock(Request $request){
        $file = $request->file('file');
        $data = Excel::toArray(new PriceStockConvertImport, $file)[0];
        array_shift($data);

        if($request->type == 'BPL'){
            // Row 0 -> Kode Barang
            // Row 1 -> Nama Barang
            // Row 2 -> Merk
            // Row 3 -> Stock Supplier
            // Row 4 -> Satuan
            // Row 5 -> Stock Baru
            foreach($data as &$row){
                switch($row[4]){
                    case "BTL":
                        $row[5] = round((float) $row[3] / 4, 2);
                        break;
                    case "FLS":
                        $row[5] = round((float) $row[3] / 4, 2);
                        break;
                    case "TUBE":
                        $row[5] = round((float) $row[3] / 10, 2);
                        break;
                    case "TUB":
                        $row[5] = round((float) $row[3] / 10, 2);
                        break;
                    default:
                        $row[5] = (float) $row[3];
                }

                unset($row[2]);
                $row = array_values($row);
            }
            
            return Excel::download(new StockConvertBPLExport($data), 'STOCK-BPL-CONVERTED-'.Carbon::now().'.xlsx');
        }
        else{
            // Row 0 -> Nama Barang
            // Row 1 -> Disc
            // Row 2 -> HNA
            // Row 3 -> Stock

            foreach($data as $key => &$row){
                if((float)$row[1] < 12){
                    unset($data[$key]);
                    continue;
                }

                $row[3] = 50; 
            }
            
            $data = array_values($data);
            
            return Excel::download(new StockConvertACMExport($data), 'STOCK-ACM-CONVERTED-'.Carbon::now().'.xlsx');
        }
    }
}

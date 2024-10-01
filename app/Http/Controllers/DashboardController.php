<?php

namespace App\Http\Controllers;

use App\Models\BuyOrder;
use App\Models\ConsignmentRequest;
use App\Models\Item;
use App\Models\Note;
use App\Models\Partner;
use App\Models\PartnerItem;
use App\Models\SellOrder;
use App\Models\SellOrderType;
use App\Models\Supplier;
use App\Models\Type;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request){
        if($request->ajax()){
            // Notes
            $notes = Note::orderBy('updated_at', 'DESC')->get();

            return DataTables::of($notes)
            ->addColumn('show', function ($p) {
                $returnedValue = [];

                array_push($returnedValue, [
                    "description" => $p->description, 
                    'id' => $p->id,
                ]);

                return $returnedValue;
            })
            ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $filterStart = $request->filterStart ?? '';
        $filterEnd = $request->filterEnd ?? '';

        $queryBuy = BuyOrder::where('status_id', 2);
        $querySell = SellOrder::where('status_id', 2)->whereIn('sell_order_type_id', [1, 5]);

        if ($filterStart != '') {
            $queryBuy->where('arrived_at', '>=', $filterStart);
            $querySell->where('delivered_at', '>=', $filterStart);
        }

        if ($filterEnd != '') {
            $queryBuy->where('arrived_at', '<=', $filterEnd);
            $querySell->where('delivered_at', '<=', $filterEnd);
        }

        $buys = $queryBuy->get();
        $sells = $querySell->get();

        $totalBuy = 0;

        foreach($buys as $buy){
            foreach($buy->items as $item){
                $totalBuy += $item->pivot->amount;
            }
        }

        $totalSell = 0;

        foreach($sells as $sell){
            foreach($sell->partnerItems as $item){
                $totalSell += $item->pivot->total;
            }
        }

        $partnerItems = PartnerItem::where('stock_qty', '>', '0')->get();

        $totalAssets = [
            'pusat' => 0,
            'mitra' => 0
        ];

        foreach($partnerItems as $partnerItem){
            if($partnerItem->partner_id == 1){
                $totalAssets['pusat'] += $partnerItem->stock_qty * $partnerItem->discount_price;
            }
            else{
                $totalAssets['mitra'] += $partnerItem->stock_qty * $partnerItem->discount_price;
            }
        }

        $currentDatePlus2W = Carbon::today()->addWeeks(2)->addDay(1)->toDateString();
        
        $konsinyasiDues = SellOrder::where('sell_order_type_id', 2)->where('due_at', '!=', null)->where('due_at', '<=', $currentDatePlus2W)->orderBy('due_at', 'ASC')->take(5)->get()->transform(function ($dt){
            $dt->diff = Carbon::parse($dt->due_at)->diffForHumans();

            $dt->diff = str_replace(['ago', 'weeks', 'week', 'days', 'day', 'from now', 'hours', 'hour', 'minutes', 'minute', 'seconds', 'second'], ['yang lalu', 'minggu', 'minggu', 'hari', 'hari', 'dari sekarang', 'jam', 'jam', 'menit', 'menit', 'detik', 'detik'], $dt->diff);

            return $dt;
        });

        // Define Top
        $sellOrderTop5PenjualanTertinggi = SellOrder::where('sell_order_type_id', 5)->where('status_id', 2);
        $sellOrderTop5Regular = SellOrder::where('sell_order_type_id', 1)->where('status_id', 2);    
        $sellOrderTop5Konsinyasi = SellOrder::where('sell_order_type_id', 2)->where('status_id', 2);
        $sellOrderTop20FastMoving = SellOrder::whereIn('sell_order_type_id', [1, 5])->where('status_id', 2);
        
        if ($filterStart != '') {
            $sellOrderTop5PenjualanTertinggi->where('delivered_at', '>=', $filterStart);
            $sellOrderTop5Regular->where('delivered_at', '>=', $filterStart);
            $sellOrderTop5Konsinyasi->where('delivered_at', '>=', $filterStart);
            $sellOrderTop20FastMoving->where('delivered_at', '>=', $filterStart);
        }

        if ($filterEnd != ''){
            $sellOrderTop5PenjualanTertinggi->where('delivered_at', '<=', $filterEnd);
            $sellOrderTop5Regular->where('delivered_at', '<=', $filterEnd);
            $sellOrderTop5Konsinyasi->where('delivered_at', '<=', $filterEnd);
            $sellOrderTop20FastMoving->where('delivered_at', '<=', $filterEnd);
        }

        
        $sellOrderTop5PenjualanTertinggi = $sellOrderTop5PenjualanTertinggi->get();
        $sellOrderTop5Regular = $sellOrderTop5Regular->get();
        $sellOrderTop5Konsinyasi = $sellOrderTop5Konsinyasi->get();
        $sellOrderTop20FastMoving = $sellOrderTop20FastMoving->get();
        
        // Top 5 Penjualan Tertinggi
        $collectionDefaultTop5PenjualanTertinggi = collect(['partner_id' => 0, 'value' => 0.0]);

        $collectionArrayTop5PenjualanTertinggi = collect();

        foreach($sellOrderTop5PenjualanTertinggi as $so){
            $totalThis = 0;

            foreach($so->partnerItems as $pa){
                $totalThis += $pa->pivot->total;
            }

            $partnerId = $so->destination_partner_id;

            // Check if the partner_id already exists in the collectionArrayTop5PenjualanTertinggi
            $existingPartner = $collectionArrayTop5PenjualanTertinggi->first(function ($item) use ($partnerId) {
                return $item['partner_id'] === $partnerId;
            });

            if ($existingPartner) {
                // Update the value for the existing partner
                $existingPartner['value'] += $totalThis;
            } else {
                // Add a new partner with the given partner_id and value
                $newPartner = ['partner_id' => $partnerId, 'value' => $totalThis];
                $collectionArrayTop5PenjualanTertinggi[] = $newPartner;
            }
        }

        $collectionArrayTop5PenjualanTertinggi = $collectionArrayTop5PenjualanTertinggi->sortByDesc('value')->values();

        $arrayPartner = [];
        foreach($collectionArrayTop5PenjualanTertinggi as $kons){
            array_push($arrayPartner, $kons['partner_id']);
        }

        $top5PenjualanTertinggi = Partner::whereIn('id', $arrayPartner);
        if(count($arrayPartner) > 1)
            $top5PenjualanTertinggi = $top5PenjualanTertinggi->orderByRaw("FIELD(id, " . implode(',', $arrayPartner) . ")");
        $top5PenjualanTertinggi = $top5PenjualanTertinggi->take(5)->get();

        // Top 5 Regular
        $collectionDefaultTop5Regular = collect(['partner_id' => 0, 'value' => 0.0]);

        $collectionArrayTop5Regular = collect();

        foreach($sellOrderTop5Regular as $so){
            $totalThis = 0;

            foreach($so->partnerItems as $pa){
                $totalThis += $pa->pivot->total;
            }

            $partnerId = $so->destination_partner_id;

            // Check if the partner_id already exists in the collectionArrayTop5Regular
            $existingPartner = $collectionArrayTop5Regular->first(function ($item) use ($partnerId) {
                return $item['partner_id'] === $partnerId;
            });

            if ($existingPartner) {
                // Update the value for the existing partner
                $existingPartner['value'] += $totalThis;
            } else {
                // Add a new partner with the given partner_id and value
                $newPartner = ['partner_id' => $partnerId, 'value' => $totalThis];
                $collectionArrayTop5Regular[] = $newPartner;
            }
        }

        $collectionArrayTop5Regular = $collectionArrayTop5Regular->sortByDesc('value')->values();

        $arrayPartner = [];
        foreach($collectionArrayTop5Regular as $kons){
            array_push($arrayPartner, $kons['partner_id']);
        }

        $top5Regular = Partner::whereIn('id', $arrayPartner);
        if(count($arrayPartner) > 1)
            $top5Regular = $top5Regular->orderByRaw("FIELD(id, " . implode(',', $arrayPartner) . ")");
        $top5Regular = $top5Regular->take(5)->get();

        // Top 5 Konsinyasi
        $collectionDefaultTop5Konsinyasi = collect(['partner_id' => 0, 'value' => 0]);

        $collectionArrayTop5Konsinyasi = collect();

        foreach($sellOrderTop5Konsinyasi as $so){
            $totalThis = 0;

            foreach($so->partnerItems as $pa){
                $totalThis += $pa->pivot->quantity;
            }

            $partnerId = $so->destination_partner_id;

            // Check if the partner_id already exists in the collectionArrayTop5Konsinyasi
            $existingPartner = $collectionArrayTop5Konsinyasi->first(function ($item) use ($partnerId) {
                return $item['partner_id'] === $partnerId;
            });

            if ($existingPartner) {
                // Update the value for the existing partner
                $existingPartner['value'] += $totalThis;
            } else {
                // Add a new partner with the given partner_id and value
                $newPartner = ['partner_id' => $partnerId, 'value' => $totalThis];
                $collectionArrayTop5Konsinyasi[] = $newPartner;
            }
        }

        $collectionArrayTop5Konsinyasi = $collectionArrayTop5Konsinyasi->sortByDesc('value')->values();

        $arrayPartner = [];
        foreach($collectionArrayTop5Konsinyasi as $kons){
            array_push($arrayPartner, $kons['partner_id']);
        }

        $top5Konsinyasi = Partner::whereIn('id', $arrayPartner);
        if(count($arrayPartner) > 1)
            $top5Konsinyasi = $top5Konsinyasi->orderByRaw("FIELD(id, " . implode(',', $arrayPartner) . ")");
        $top5Konsinyasi = $top5Konsinyasi->take(5)->get();

        // Top 20 FastMoving
        $collectionDefaultTop20FastMoving = collect(['item_id' => 0, 'value' => 0]);

        $collectionArrayTop20FastMoving = collect();

        foreach($sellOrderTop20FastMoving as $so){
            foreach($so->partnerItems as $pa){
                $totalThis = $pa->pivot->quantity;
                $item_id = $pa->item_id;

                $existingItem = $collectionArrayTop20FastMoving->first(function ($item) use ($item_id) {
                    return $item['item_id'] === $item_id;
                });
                
                if ($existingItem) {
                    // Update the value for the existing partner by creating a new item with updated value
                    $updatedItem = ['item_id' => $existingItem['item_id'], 'value' => $existingItem['value'] + $totalThis];
                    $collectionArrayTop20FastMoving->transform(function ($item) use ($updatedItem) {
                        return $item['item_id'] === $updatedItem['item_id'] ? $updatedItem : $item;
                    });
                } else {
                    // Add a new partner with the given item_id and value
                    $newItem = ['item_id' => $item_id, 'value' => $totalThis];
                    $collectionArrayTop20FastMoving->push($newItem);
                }
            }
        }

        $collectionArrayTop20FastMoving = $collectionArrayTop20FastMoving->sortByDesc('value')->values();

        $arrayItem = [];
        foreach($collectionArrayTop20FastMoving as $kons){
            array_push($arrayItem, $kons['item_id']);
        }

        $top20FastMoving = Item::whereIn('id', $arrayItem);
        if(count($arrayItem) > 1)
            $top20FastMoving = $top20FastMoving->orderByRaw("FIELD(id, " . implode(',', $arrayItem) . ")");
        $top20FastMoving = $top20FastMoving->take(20)->get();
        
        if($filterStart != ''){
            $filterStartName = Carbon::createFromFormat('Y-m-d', $filterStart)->format('d-m-Y');
        }
        else{
            $filterStartName = '-';
        }

        if($filterEnd != ''){
            $filterEndName = Carbon::createFromFormat('Y-m-d', $filterEnd)->format('d-m-Y');
        }
        else{
            $filterEndName = '-';
        }

        $route = 'dashboard';

        return view('dashboard', compact('totalBuy', 'totalSell', 'totalAssets', 'konsinyasiDues', 'top5Regular', 'top5Konsinyasi', 'top5PenjualanTertinggi', 'top20FastMoving', 'filterStart', 'filterEnd', 'filterStartName', 'filterEndName', 'success', 'error', 'route'));
    }

    public function newIndex(Request $request){
        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $filterStart = $request->filterStart ?? '';
        $filterEnd = $request->filterEnd ?? '';

        $suppliersCount = Supplier::all()->count();
        $zonesCount = Zone::all()->count();
        $partnersCount = Partner::all()->count();
        $itemSoldCount = Item::select('items.id')
                            ->join('partner_item', 'partner_item.item_id', '=', 'items.id')
                            ->join('sell_order_details', 'sell_order_details.item_id', '=', 'partner_item.id')
                            ->join('sell_orders', 'sell_orders.id', '=', 'sell_order_details.sell_order_id')
                            ->where('sell_order_details.quantity', '>', '0')
                            ->where('sell_orders.status_id', 2)
                            ->groupBy('items.id')
                            ->get()->count();
        $sellListCount = SellOrder::where('status_id', 2)->whereIn('sell_order_type_id', [1, 5])->get()->count();
        $scheduleSOCount = SellOrder::selectRaw('sell_orders.id')
                                    ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                                    ->where('status_id', 2)
                                    ->whereNotNull('due_at')
                                    ->where('sell_orders.sell_order_type_id', 2)
                                    ->where('status_kode', 'FIRST')
                                    ->where(function($query) use($filterStart, $filterEnd){
                                        if($filterStart != ''){
                                            $query->where('due_at', '>=', $filterStart);
                                        }

                                        if($filterStart != ''){
                                            $query->where('due_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('sell_orders.id')
                                    ->get()->count();
        $route = 'dashboard';

        return view('dashboard-v2', compact('filterStart', 'filterEnd', 'success', 'error', 'route', 'suppliersCount', 'zonesCount', 'partnersCount', 'itemSoldCount', 'sellListCount', 'scheduleSOCount'));
    }

    public function assetGudang(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $pusat = PartnerItem::withoutGlobalScope('order')
                            ->selectRaw('SUM(stock_qty) as jumlah_obat')
                            ->selectRaw('SUM(items.price * partner_item.stock_qty) as total_value')    
                            ->join('items', 'partner_item.item_id', '=', 'items.id')
                            ->where('is_consigned', 0)
                            ->where('stock_qty', '>', 0)
                            ->where(function($query) use($filterStart, $filterEnd){
                                if($filterStart != 'ALL'){
                                    $query->where('partner_item.created_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('partner_item.created_at', '<=', $filterEnd);
                                }
                            })
                            ->first();

        $mitraJumlah = PartnerItem::withoutGlobalScope('order')
                            ->selectRaw('SUM(stock_qty) as jumlah_obat')
                            ->join('items', 'partner_item.item_id', '=', 'items.id')
                            ->where('is_consigned', 1)
                            ->where('stock_qty', '>', 0)
                            ->where(function($query) use($filterStart, $filterEnd){
                                if($filterStart != 'ALL'){
                                    $query->where('partner_item.created_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('partner_item.created_at', '<=', $filterEnd);
                                }
                            })
                            ->first();

        $mitraValue = DB::table('sell_order_details as sod')
                            ->selectRaw('SUM((total/quantity) * quantity) as total_value')
                            ->join('sell_orders as so', 'so.id', '=', 'sod.sell_order_id')
                            ->join('partner_item as pi2', 'pi2.id', '=', 'sod.item_id')
                            ->join('items as i', 'i.id', '=', 'pi2.item_id')
                            ->where('quantity', '<>', 0)
                            ->where('so.status_id', '<>', 3)
                            ->where(function($query) use($filterStart, $filterEnd){
                                if($filterStart != 'ALL'){
                                    $query->where('pi2.created_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('pi2.created_at', '<=', $filterEnd);
                                }
                            })
                            ->first();

        $assets = collect([
            [
                'no' => 1,
                'jenis' => 'Pusat',
                'jumlah_obat' => (int) $pusat->jumlah_obat,
                'total_aset' => 'Rp'.number_format((int) $pusat->total_value),
                'total_aset_raw' => (int) $pusat->total_value
            ],
            [
                'no' => 2,
                'jenis' => 'Mitra',
                'jumlah_obat' => (int) $mitraJumlah->jumlah_obat,
                'total_aset' => 'Rp'.number_format((int) $mitraValue->total_value),
                'total_aset_raw' => (int) $mitraValue->total_value
            ],
        ]);

        // $totalAsetSum = $assets->sum('jumlah_obat');
        // $totalValueSum = $assets->sum('total_aset_raw');

        // $assets[] = [
        //     'no' => 'Total Aset',
        //     'jenis' => $totalAsetSum,
        //     'jumlah_obat' => 'Rp'.number_format($totalValueSum),
        //     'total_aset' => ''
        // ];

        return response()->json($assets);
    }

    public function masterSKUList(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $others = Supplier::selectRaw('suppliers.name as nama_supplier')
                        ->selectRaw('COUNT(items.id) as sku_tersedia')
                        ->selectRaw('SUM(partner_item.stock_qty) as jumlah_obat')
                        ->leftJoin('items', 'suppliers.id', '=', 'items.supplier_id')
                        ->leftJoin('partner_item', 'items.id', '=', 'partner_item.item_id')
                        ->where('stock_qty', '>', '0')
                        ->where('is_consigned', '=', '0')
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('partner_item.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('partner_item.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('suppliers.id', 'suppliers.name')
                        ->get();

        $jumlahSKU = Supplier::selectRaw('suppliers.name as nama_supplier')
                        ->selectRaw('COUNT(items.id) as jumlah_sku')    
                        ->leftJoin('items', 'items.supplier_id', '=', 'suppliers.id')
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('items.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('items.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('suppliers.id', 'suppliers.name')
                        ->get()
                        ->transform(function ($dt) use($others){
                            $isFind = false;

                            foreach($others as $other){
                                if($other->nama_supplier == $dt->nama_supplier){
                                    $isFind = true;

                                    $dt->sku_tersedia = $other->sku_tersedia;
                                    $dt->jumlah_obat = $other->jumlah_obat;
                                }
                            }

                            if(!$isFind){
                                $dt->sku_tersedia = 0;
                                $dt->jumlah_obat = 0;
                            }

                            return $dt;
                        });

        $assets = collect([]);

        $suppliers = Supplier::all();

        foreach($suppliers as $idx => $supp){
            $find = false;

            foreach($jumlahSKU as $res){
                if($res->nama_supplier == $supp->name){
                    $find = true;

                    $assets[] = [
                            'no' => $idx+1,
                            'nama_supplier' => $res->nama_supplier,
                            'jumlah_sku' => (int) $res->jumlah_sku,
                            'sku_tersedia' => (int) $res->sku_tersedia,
                            'jumlah_obat' => (int) $res->jumlah_obat
                    ];
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_supplier' => $supp->name,
                    'jumlah_sku' => 0,
                    'sku_tersedia' => 0,
                    'jumlah_obat' => 0
                ];
            }
        }

        $jumlahSKUSum = $assets->sum('jumlah_sku');
        $skuTersediaSum = $assets->sum('sku_tersedia');
        $jumlahObatSum = $assets->sum('jumlah_obat');

        $assets[] = [
            'no' => 'Total SKU',
            'nama_supplier' => $jumlahSKUSum,
            'jumlah_sku' => $skuTersediaSum,
            'sku_tersedia' => $jumlahObatSum,
            'jumlah_obat' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function penjualan(Request $request){
        $penjualanTotal = SellOrder::selectRaw("CONCAT(year(sell_orders.delivered_at), '-', MONTH(sell_orders.delivered_at)) as deliver_month, SUM(sell_order_details.total) as total")
                                    ->join('sell_order_details', 'sell_orders.id', '=', 'sell_order_details.sell_order_id')
                                    ->whereIn('sell_orders.sell_order_type_id', [1, 5])
                                    ->where('sell_orders.status_id', 2)
                                    ->whereNull('sell_orders.deleted_at')
                                    ->groupBy('deliver_month')
                                    ->orderBy('sell_orders.delivered_at', 'DESC')
                                    ->limit(10)
                                    ->get();

        $penjualanPerType = SellOrder::select('sell_order_types.name')
                                    ->selectRaw("CONCAT(year(sell_orders.delivered_at), '-', MONTH(sell_orders.delivered_at)) as deliver_month, SUM(sell_order_details.total) as total")
                                    ->join('sell_order_details', 'sell_orders.id', '=', 'sell_order_details.sell_order_id')
                                    ->join('sell_order_types', 'sell_order_types.id', '=', 'sell_orders.sell_order_type_id')
                                    ->whereIn('sell_orders.sell_order_type_id', [1, 5])
                                    ->where('sell_orders.status_id', 2)
                                    ->whereNull('sell_orders.deleted_at')
                                    ->groupBy('deliver_month', 'sell_order_types.name')
                                    ->orderBy('sell_orders.delivered_at', 'DESC')
                                    ->limit(10)
                                    ->get();

        $total = $penjualanTotal->map(function ($penjualan, int $key){
            return [
                'name' => 'Total',
                'deliver_month' => $penjualan->deliver_month,
                'total' => $penjualan->total,
                'year' => explode('-', $penjualan->deliver_month)[0],
                'month' => explode('-', $penjualan->deliver_month)[1],
            ];
        });

        $typeReguler = $penjualanPerType->filter(function ($penjualan, int $key){
            return $penjualan->name == 'Penjualan Reguler';
        })->map(function ($penjualan, int $key){
            return [
                'name' => $penjualan->name,
                'deliver_month' => $penjualan->deliver_month,
                'total' => $penjualan->total,
                'year' => explode('-', $penjualan->deliver_month)[0],
                'month' => explode('-', $penjualan->deliver_month)[1],
            ];
        });

        $typeSO = $penjualanPerType->filter(function ($penjualan, int $key) {
            return $penjualan->name == 'Stock Opname';
        })->map(function ($penjualan, int $key){
            return [
                'name' => $penjualan->name,
                'deliver_month' => $penjualan->deliver_month,
                'total' => $penjualan->total,
                'year' => explode('-', $penjualan->deliver_month)[0],
                'month' => explode('-', $penjualan->deliver_month)[1],
            ];
        });

        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonth($i);
            $year = Carbon::today()->startOfMonth()->subMonth($i)->format('Y');
            array_push($months, array(
                'month' => (int) $month->format('m'),
                'monthName' => $month->monthName,
                'year' => $year
            ));
        }

        $results = [];

        foreach($months as $month){
            $totalWithSameMonthYear = $total->filter(function($dt) use ($month){
                return $dt['month'] == $month['month'] && $dt['year'] == $month['year'];
            })->all();
            
            $regulerWithSameMonthYear = $typeReguler->filter(function($dt) use ($month){
                return $dt['month'] == $month['month'] && $dt['year'] == $month['year'];
            })->all();
            
            $SOWithSameMonthYear = $typeSO->filter(function($dt) use ($month){
                return $dt['month'] == $month['month'] && $dt['year'] == $month['year'];
            })->all();

            if(count($totalWithSameMonthYear) > 0){
                foreach($totalWithSameMonthYear as $dt){
                    $amountTotal = $dt['total'];
                }
            }

            if(count($regulerWithSameMonthYear) > 0){
                foreach($regulerWithSameMonthYear as $dt){
                    $amountReguler = $dt['total'];
                }
            }

            if(count($SOWithSameMonthYear) > 0){
                foreach($SOWithSameMonthYear as $dt){
                    $amountSO = $dt['total'];
                }
            }

            array_push($results, array(
                'label' => $month['monthName'] . '-' . $month['year'],
                'totalAmount' => count($totalWithSameMonthYear) > 0 ? (int) $amountTotal : 0,
                'regulerAmount' => count($regulerWithSameMonthYear) > 0 ? (int) $amountReguler : 0,
                'soAmount' => count($SOWithSameMonthYear) > 0 ? (int) $amountSO : 0,
            ));
        }

        return response()->json($results);
    
    }
    public function penjualanPembelian(Request $request){
        $penjualanTotal = SellOrder::selectRaw("CONCAT(year(sell_orders.delivered_at), '-', MONTH(sell_orders.delivered_at)) as deliver_month, SUM(sell_order_details.total) as total")
                                    ->join('sell_order_details', 'sell_orders.id', '=', 'sell_order_details.sell_order_id')
                                    ->whereIn('sell_orders.sell_order_type_id', [1, 5])
                                    ->where('sell_orders.status_id', 2)
                                    ->whereNull('sell_orders.deleted_at')
                                    ->groupBy('deliver_month')
                                    ->orderBy('sell_orders.delivered_at', 'DESC')
                                    ->limit(10)
                                    ->get();

        $pembelianTotal = BuyOrder::selectRaw("CONCAT(year(buy_orders.SP_date), '-', MONTH(buy_orders.SP_date)) as 'deliver_month', SUM(buy_order_details.amount) as total")
                                    ->join('buy_order_details', 'buy_orders.id', '=', 'buy_order_details.buy_order_id')
                                    ->where('buy_orders.status_id', 2)
                                    ->whereNull('buy_orders.deleted_at')
                                    ->groupBy('deliver_month')
                                    ->orderBy('buy_orders.SP_date', 'DESC')
                                    ->limit(10)
                                    ->get();

        $penjualan = $penjualanTotal->map(function ($penjualan, int $key){
            return [
                'name' => 'Penjualan',
                'deliver_month' => $penjualan->deliver_month,
                'total' => $penjualan->total,
                'year' => explode('-', $penjualan->deliver_month)[0],
                'month' => explode('-', $penjualan->deliver_month)[1],
            ];
        });

        $pembelian = $pembelianTotal->map(function ($pembelian, int $key){
            return [
                'name' => 'Pembelian',
                'deliver_month' => $pembelian->deliver_month,
                'total' => $pembelian->total,
                'year' => explode('-', $pembelian->deliver_month)[0],
                'month' => explode('-', $pembelian->deliver_month)[1],
            ];
        });

        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::today()->startOfMonth()->subMonth($i);
            $year = Carbon::today()->startOfMonth()->subMonth($i)->format('Y');
            array_push($months, array(
                'month' => (int) $month->format('m'),
                'monthName' => $month->monthName,
                'year' => $year
            ));
        }

        $results = [];

        foreach($months as $month){
            $penjualanWithSameMonthYear = $penjualan->filter(function($dt) use ($month){
                return $dt['month'] == $month['month'] && $dt['year'] == $month['year'];
            })->all();
            
            $pembelianWithSameMonthYear = $pembelian->filter(function($dt) use ($month){
                return $dt['month'] == $month['month'] && $dt['year'] == $month['year'];
            })->all();

            if(count($penjualanWithSameMonthYear) > 0){
                foreach($penjualanWithSameMonthYear as $dt){
                    $amountPenjualan = $dt['total'];
                }
            }

            if(count($pembelianWithSameMonthYear) > 0){
                foreach($pembelianWithSameMonthYear as $dt){
                    $amountPembelian = $dt['total'];
                }
            }

            array_push($results, array(
                'label' => $month['monthName'] . '-' . $month['year'],
                'penjualanAmount' => count($penjualanWithSameMonthYear) > 0 ? (int) $amountPenjualan : 0,
                'pembelianAmount' => count($pembelianWithSameMonthYear) > 0 ? (int) $amountPembelian : 0,
            ));
        }

        return response()->json($results);
    }

    public function sp(Request $request){
        $type = $request->type;
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $suppliers = Supplier::all();

        $countList = Supplier::selectRaw('suppliers.name `nama_supplier`')
                            ->selectRaw('count(buy_orders.id) `jumlah_sp`')
                            ->join('buy_orders', 'buy_orders.supplier_id', '=', 'suppliers.id')
                            ->join('types', 'types.id', '=', 'buy_orders.type_id')
                            ->where('buy_orders.status_id', 2)
                            ->where(function($query) use($type, $filterStart, $filterEnd){
                                if($type != 'ALL'){
                                    $query->where('types.id', $type);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('buy_orders.created_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('buy_orders.created_at', '<=', $filterEnd);
                                }
                            })
                            ->groupBy('suppliers.id')
                            ->get();

        $results = Supplier::selectRaw('suppliers.name `nama_supplier`')
                        ->selectRaw('sum(buy_order_details.qty_came) `jumlah_obat`')
                        ->selectRaw('sum(buy_order_details.qty_came * buy_order_details.buy_price) `total_harga`')
                        ->join('buy_orders', 'buy_orders.supplier_id', '=', 'suppliers.id')
                        ->join('types', 'types.id', '=', 'buy_orders.type_id')
                        ->join('buy_order_details', 'buy_order_details.buy_order_id', '=', 'buy_orders.id')
                        ->where('buy_orders.status_id', 2)
                        ->where(function($query) use($type, $filterStart, $filterEnd){
                            if($type != 'ALL'){
                                $query->where('types.id', $type);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('buy_orders.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('buy_orders.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('suppliers.id')
                        ->get();
        
        $assets = collect([]);

        foreach($suppliers as $idx => $supp){
            $find = false;

            foreach($results as $res){
                if($res->nama_supplier == $supp->name){

                    foreach($countList as $res2){
                        if($res->nama_supplier == $res2->nama_supplier){
                            $find = true;
        
                            $assets[] = [
                                    'no' => $idx+1,
                                    'nama_supplier' => $res->nama_supplier,
                                    'jumlah_sp' => (int) $res2->jumlah_sp,
                                    'jumlah_obat' => (int) $res->jumlah_obat,
                                    'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                    'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_supplier' => $supp->name,
                    'jumlah_sp' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $totalJumlahSPSum = $assets->sum('jumlah_sp');
        $totaljumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');

        $assets[] = [
            'no' => 'Total SP',
            'nama_supplier' => $totalJumlahSPSum,
            'jumlah_sp' => $totaljumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => '',
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listPermintaan(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $countList = ConsignmentRequest::selectRaw('type as jenis')
                                    ->selectRaw('COUNT(id) `jumlah_permintaan`')
                                    ->where('status_id', '=', '4')
                                    ->where(function($query) use($filterStart, $filterEnd){
                                        if($filterStart != 'ALL'){
                                            $query->where('consignment_requests.created_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('consignment_requests.created_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('type')
                                    ->get();

        $results = ConsignmentRequest::selectRaw('type as jenis')
                                    ->selectRaw('COUNT(items.id) as sku_tersedia')
                                    ->selectRaw('SUM(quantity_send) `jumlah_obat`')
                                    ->selectRaw('SUM(quantity_send * items.price) `total_harga`')
                                    ->join('consignment_request_details', 'consignment_request_details.consignment_request_id', '=', 'consignment_requests.id')
                                    ->join('items', 'items.id', '=', 'consignment_request_details.item_id')
                                    ->where('status_id', '=', '4')
                                    ->where('consignment_request_details.quantity_send', '>', '0')
                                    ->where(function($query) use($filterStart, $filterEnd){
                                        if($filterStart != 'ALL'){
                                            $query->where('consignment_requests.created_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('consignment_requests.created_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('type')
                                    ->get();
        
        $assets = collect([]);

        $types = ['Reguler', 'Konsinyasi'];

        foreach($types as $idx => $type){
            $find = false;

            foreach($results as $res){
                if($res->jenis == $type){
                    foreach($countList as $res2){
                        if($res2->jenis == $res->jenis){
                            $find = true;
        
                            $assets[] = [
                                'no' => $idx+1,
                                'jenis' => $res2->jenis,
                                'jumlah_permintaan' => (int) $res2->jumlah_permintaan,
                                'jumlah_obat' => (int) $res->jumlah_obat,
                                'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'jenis' => $type,
                    'jumlah_permintaan' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahPermintaanSum = $assets->sum('jumlah_permintaan');
        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');

        $assets[] = [
            'no' => 'Total Permintaan',
            'jenis' => $jumlahPermintaanSum,
            'jumlah_permintaan' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listPengiriman(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $countList = SellOrderType::selectRaw('sell_order_types.name `jenis`')
                                    ->selectRaw('COUNT(sell_orders.id) `jumlah_pengiriman`')
                                    ->join('sell_orders', 'sell_order_types.id', '=', 'sell_orders.sell_order_type_id')
                                    ->where('status_id', '2')
                                    ->whereIn('sell_order_types.id', [1, 2])
                                    ->where(function($query) use($filterStart, $filterEnd){
                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('sell_order_types.id')
                                    ->get();

        $results = SellOrderType::selectRaw('sell_order_types.name `jenis`')
                                    ->selectRaw('sum(sell_order_details.quantity) `jumlah_obat`')
                                    ->selectRaw('sum(sell_order_details.total) `total_harga`')
                                    ->join('sell_orders', 'sell_order_types.id', '=', 'sell_orders.sell_order_type_id')
                                    ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                                    ->where('status_id', '2')
                                    ->whereIn('sell_order_types.id', [1, 2])
                                    ->where(function($query) use($filterStart, $filterEnd){
                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('sell_order_types.id')
                                    ->get();
        
        $assets = collect([]);

        $types = SellOrderType::whereIn('sell_order_types.id', [1, 2])->get();

        foreach($types as $idx => $type){
            $find = false;

            foreach($results as $res){
                if($res->jenis == 'Penjualan Reguler')
                    $res->jenis = 'Reguler';
                
                if($type->name == 'Penjualan Reguler')
                    $type->name = 'Reguler';
    
                if($type->name == $res->jenis){
                    foreach($countList as $res2){
                        if($res2->jenis == 'Penjualan Reguler')
                            $res2->jenis = 'Reguler';

                        if($res2->jenis == $res->jenis){
                            $find = true;
        
                            $assets[] = [
                                'no' => $idx+1,
                                'jenis' => $res->jenis,
                                'jumlah_pengiriman' => (int) $res2->jumlah_pengiriman,
                                'jumlah_obat' => (int) $res->jumlah_obat,
                                'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                if($type->name == 'Penjualan Reguler')
                            $type->name = 'Reguler';
                
                $assets[] = [
                    'no' => $idx+1,
                    'jenis' => $type->name,
                    'jumlah_pengiriman' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahPengirimanSum = $assets->sum('jumlah_pengiriman');
        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');

        $assets[] = [
            'no' => 'Total Pengiriman',
            'jenis' => $jumlahPengirimanSum,
            'jumlah_pengiriman' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function so(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $countList = Zone::selectRaw('zones.name `wilayah`')
                        ->selectRaw('COUNT(sell_orders.id) `jumlah_so`')
                        ->join('partner_zone', 'partner_zone.zone_id', '=', 'zones.id')
                        ->join('partners', 'partners.id', '=', 'partner_zone.partner_id')
                        ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                        ->where('sell_orders.sell_order_type_id', '5')
                        ->where('sell_orders.status_id', 2)
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('zones.id')
                        ->get();

        $results = Zone::selectRaw('zones.name `wilayah`')
                        ->selectRaw('SUM(sell_order_details.quantity) `jumlah_obat`')
                        ->selectRaw('SUM(sell_order_details.total) `total_harga`')
                        ->join('partner_zone', 'partner_zone.zone_id', '=', 'zones.id')
                        ->join('partners', 'partners.id', '=', 'partner_zone.partner_id')
                        ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                        ->join('sell_order_details', 'sell_orders.id', '=', 'sell_order_details.sell_order_id')
                        ->where('sell_orders.sell_order_type_id', '5')
                        ->where('sell_orders.status_id', 2)
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('zones.id')
                        ->get();
        
        $assets = collect([]);

        $zones = Zone::all();

        foreach($zones as $idx => $zone){
            $find = false;

            foreach($results as $res){
                if($zone->name == $res->wilayah){

                    foreach($countList as $res2){
                        if($res->wilayah == $res2->wilayah){
                            $find = true;
        
                            $assets[] = [
                                'no' => $idx+1,
                                'wilayah' => $res->wilayah,
                                'jumlah_so' => (int) $res2->jumlah_so,
                                'jumlah_obat' => (int) $res->jumlah_obat,
                                'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'wilayah' => $zone->name,
                    'jumlah_so' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahSOSum = $assets->sum('jumlah_so');
        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');

        $assets = $assets->sortByDesc('total_harga_raw');

        $assets[] = [
            'no' => 'Total SO',
            'wilayah' => $jumlahSOSum,
            'jumlah_so' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listPermintaanKlinik(Request $request){
        $type = $request->type;
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $countList = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                            ->selectRaw('COUNT(consignment_requests.id) `jumlah_permintaan`')
                            ->join('consignment_requests', 'consignment_requests.partner_id', '=', 'partners.id')
                            ->where('status_id', '=', '4')
                            ->where(function($query) use($filterStart, $filterEnd, $type){
                                if($type != 'ALL'){
                                    $query->where('consignment_requests.type', $type);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('consignment_requests.created_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('consignment_requests.created_at', '<=', $filterEnd);
                                }
                            })
                            ->groupBy('partners.id')
                            ->get();

        $results = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                        ->selectRaw('SUM(quantity_send) `jumlah_obat`')
                        ->selectRaw('SUM(quantity_send * items.price) `total_harga`')
                        ->join('consignment_requests', 'consignment_requests.partner_id', '=', 'partners.id')
                        ->join('consignment_request_details', 'consignment_request_details.consignment_request_id', '=', 'consignment_requests.id')
                        ->join('items', 'items.id', '=', 'consignment_request_details.item_id')
                        ->where('status_id', '=', '4')
                        ->where('consignment_request_details.quantity_send', '>', '0')
                        ->where(function($query) use($filterStart, $filterEnd, $type){
                            if($type != 'ALL'){
                                $query->where('consignment_requests.type', $type);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('consignment_requests.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('consignment_requests.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('partners.id')
                        ->get();
        
        $assets = collect([]);

        $partners = Partner::all();

        foreach($partners as $idx => $partner){
            $find = false;

            foreach($results as $res){
                if($res->nama_klinik == ($partner->clinic_id.' - '.$partner->name)){

                    foreach($countList as $res2){
                        if($res2->nama_klinik == $res->nama_klinik){
                            $find = true;
        
                            $assets[] = [
                                'no' => $idx+1,
                                'nama_klinik' => $res->nama_klinik,
                                'jumlah_permintaan' => (int) $res2->jumlah_permintaan,
                                'jumlah_obat' => (int) $res->jumlah_obat,
                                'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_klinik' => ($partner->clinic_id.' - '.$partner->name),
                    'jumlah_permintaan' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahPermintaanSum = $assets->sum('jumlah_permintaan');
        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');

        $assets = $assets->sortByDesc('total_harga_raw');

        $assets[] = [
            'no' => 'Total Permintaan',
            'nama_klinik' => $jumlahPermintaanSum,
            'jumlah_permintaan' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listPengirimanKlinik(Request $request){
        $type = $request->type;
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $countList = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                        ->selectRaw('COUNT(sell_orders.id) `jumlah_pengiriman`')
                        ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                        ->where('status_id', '=', '2')
                        ->whereIn('sell_orders.sell_order_type_id', [1, 2])
                        ->where(function($query) use($filterStart, $filterEnd, $type){
                            if($type != 'ALL'){
                                $query->where('sell_orders.sell_order_type_id', $type);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('partners.id')
                        ->get();

        $results = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                                    ->selectRaw('sum(sell_order_details.quantity) `jumlah_obat`')
                                    ->selectRaw('sum(sell_order_details.total) `total_harga`')
                                    ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                                    ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                                    ->where('status_id', '=', '2')
                                    ->whereIn('sell_orders.sell_order_type_id', [1, 2])
                                    ->where(function($query) use($filterStart, $filterEnd, $type){
                                        if($type != 'ALL'){
                                            $query->where('sell_orders.sell_order_type_id', $type);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('partners.id')
                                    ->get();
        
        $assets = collect([]);

        $partners = Partner::all();

        foreach($partners as $idx => $partner){
            $find = false;

            foreach($results as $res){
                if($res->nama_klinik == ($partner->clinic_id.' - '.$partner->name)){

                    foreach($countList as $res2){
                        if($res2->nama_klinik == $res->nama_klinik){
                            $find = true;
        
                            $assets[] = [
                                'no' => $idx+1,
                                'nama_klinik' => $res->nama_klinik,
                                'jumlah_pengiriman' => (int) $res2->jumlah_pengiriman,
                                'jumlah_obat' => (int) $res->jumlah_obat,
                                'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_klinik' => ($partner->clinic_id.' - '.$partner->name),
                    'jumlah_pengiriman' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahPengirimanSum = $assets->sum('jumlah_pengiriman');
        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');
        
        $assets = $assets->sortByDesc('total_harga_raw');

        $assets[] = [
            'no' => 'Total Pengiriman',
            'nama_klinik' => $jumlahPengirimanSum,
            'jumlah_pengiriman' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listPenjualanKlinik(Request $request){
        $type = $request->type;
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $results = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                                    ->selectRaw('sum(sell_order_details.quantity) `jumlah_obat`')
                                    ->selectRaw('sum(sell_order_details.total) `total_harga`')
                                    ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                                    ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                                    ->where('status_id', '=', '2')
                                    ->whereIn('sell_orders.sell_order_type_id', [1, 5])
                                    ->where(function($query) use($filterStart, $filterEnd, $type){
                                        if($type != 'ALL'){
                                            $query->where('sell_orders.sell_order_type_id', $type);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('partners.id')
                                    ->get();
        
        $assets = collect([]);

        $partners = Partner::all();

        foreach($partners as $idx => $partner){
            $find = false;

            foreach($results as $res){
                if($res->nama_klinik == ($partner->clinic_id.' - '.$partner->name)){
                    $find = true;

                    $assets[] = [
                        'no' => $idx+1,
                        'nama_klinik' => $res->nama_klinik,
                        'jumlah_obat' => (int) $res->jumlah_obat,
                        'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                        'total_harga_raw' => (int) $res->total_harga
                    ];
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_klinik' => ($partner->clinic_id.' - '.$partner->name),
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');
        
        $assets = $assets->sortByDesc('total_harga_raw');

        $assets[] = [
            'no' => 'Total Penjualan',
            'nama_klinik' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listPenjualanKlinikDoc(Request $request){
        $type = $request->type;
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $results = SellOrder::selectRaw('sell_orders.document_number `no_doc`')
                                    ->selectRaw('sum(sell_order_details.quantity) `jumlah_obat`')
                                    ->selectRaw('sum(sell_order_details.total) `total_harga`')
                                    ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                                    ->where('status_id', '=', '2')
                                    ->whereIn('sell_orders.sell_order_type_id', [1, 5])
                                    ->where(function($query) use($filterStart, $filterEnd, $type){
                                        if($type != 'ALL'){
                                            $query->where('sell_orders.sell_order_type_id', $type);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('sell_orders.id')
                                    ->get();
        
        $assets = collect([]);

        if(count($results) != 0){
            foreach($results as $idx => $res){
                $assets[] = [
                    'no' => $idx+1,
                    'no_doc' => $res->no_doc,
                    'jumlah_obat' => (int) $res->jumlah_obat,
                    'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                    'total_harga_raw' => (int) $res->total_harga
                ];
            }
    
            $jumlahObatSum = $assets->sum('jumlah_obat');
            $totalHargaSum = $assets->sum('total_harga_raw');
            
            $assets = $assets->sortByDesc('total_harga_raw');
    
            $assets[] = [
                'no' => 'Total Penjualan',
                'no_doc' => $jumlahObatSum,
                'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
                'total_harga' => ''
            ];
        }


        return DataTables::of($assets)
            ->make();
    }

    public function listMitra(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $results = Zone::selectRaw('zones.name `wilayah`')
                        ->selectRaw('COUNT(partners.id) `jumlah_mitra`')
                        ->join('partner_zone', 'partner_zone.zone_id', '=', 'zones.id')
                        ->join('partners', 'partners.id', '=', 'partner_zone.partner_id')
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('partners.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('partners.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('zones.id')
                        ->get();
        
        $assets = collect([]);

        $zones = Zone::all();

        foreach($zones as $idx => $zone){
            $find = false;

            foreach($results as $res){
                if($res->wilayah == $zone->name){
                    $find = true;

                    $assets[] = [
                        'no' => $idx+1,
                        'wilayah' => $res->wilayah,
                        'jumlah_mitra' => (int) $res->jumlah_mitra,
                    ];
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'wilayah' => ($zone->name),
                    'jumlah_mitra' => 0,
                ];
            }
        }

        $jumlahMitraSum = $assets->sum('jumlah_mitra');
        
        $assets = $assets->sortByDesc('total_harga_raw');

        $assets[] = [
            'no' => 'Total Mitra',
            'wilayah' => $jumlahMitraSum,
            'jumlah_mitra' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function listSOKlinik(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $countSO = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                            ->selectRaw('COUNT(sell_orders.id) `jumlah_so`')
                            ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                            ->where('status_id', '=', '2')
                            ->where('sell_orders.sell_order_type_id', 5)
                            ->where(function($query) use($filterStart, $filterEnd){
                                if($filterStart != 'ALL'){
                                    $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                }
                            })
                            ->groupBy('partners.id')
                            ->get();

        $others = Partner::selectRaw('concat(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                                    ->selectRaw('sum(sell_order_details.quantity) `jumlah_obat`')
                                    ->selectRaw('sum(sell_order_details.total) `total_harga`')
                                    ->join('sell_orders', 'sell_orders.destination_partner_id', '=', 'partners.id')
                                    ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                                    ->where('status_id', '=', '2')
                                    ->where('sell_orders.sell_order_type_id', 5)
                                    ->where(function($query) use($filterStart, $filterEnd){
                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '>=', $filterStart);
                                        }

                                        if($filterStart != 'ALL'){
                                            $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                                        }
                                    })
                                    ->groupBy('partners.id')
                                    ->get();

        $assets = collect([]);

        $partners = Partner::all();

        foreach($partners as $idx => $partner){
            $find = false;

            foreach($others as $res){
                if($res->nama_klinik == ($partner->clinic_id.' - '.$partner->name)){
                    
                    foreach($countSO as $resCount){
                        if($res->nama_klinik == $resCount->nama_klinik){
                            $find = true;

                            $assets[] = [
                                'no' => $idx+1,
                                'nama_klinik' => $res->nama_klinik,
                                'jumlah_so' => (int) $resCount->jumlah_so,
                                'jumlah_obat' => (int) $res->jumlah_obat,
                                'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                                'total_harga_raw' => (int) $res->total_harga
                            ];
                        }
                    }
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_klinik' => ($partner->clinic_id.' - '.$partner->name),
                    'jumlah_so' => 0,
                    'jumlah_obat' => 0,
                    'total_harga' => 'Rp'.number_format(0),
                    'total_harga_raw' => 0
                ];
            }
        }

        $jumlahSOSum = $assets->sum('jumlah_so');
        $jumlahObatSum = $assets->sum('jumlah_obat');
        $totalHargaSum = $assets->sum('total_harga_raw');
        
        $assets = $assets->sortByDesc('total_harga_raw');

        $assets[] = [
            'no' => 'Total SO',
            'nama_klinik' => $jumlahSOSum,
            'jumlah_so' => $jumlahObatSum,
            'jumlah_obat' => 'Rp'.number_format((int) $totalHargaSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }

    public function obatTerlaris(Request $request){
        $type = $request->type;
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $results = Item::withoutGlobalScope('order')
                        ->selectRaw('CONCAT(items.sku, " - ", items.name) `nama_obat`')
                        ->selectRaw('suppliers.name `supplier`')
                        ->selectRaw('SUM(sell_order_details.quantity) `jumlah_obat`')
                        ->selectRaw('SUM(sell_order_details.total) `total_harga`')
                        ->join('suppliers', 'items.supplier_id', '=', 'suppliers.id')
                        ->join('partner_item', 'partner_item.item_id', '=', 'items.id')
                        ->join('sell_order_details', 'sell_order_details.item_id', '=', 'partner_item.id')
                        ->join('sell_orders', 'sell_orders.id', '=', 'sell_order_details.sell_order_id')
                        ->where('sell_order_details.quantity', '>', '0')
                        ->where('sell_orders.status_id', 2)
                        ->where(function($query) use($filterStart, $filterEnd, $type){
                            if($type != 'ALL'){
                                $query->where('sell_orders.sell_order_type_id', $type);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('sell_orders.delivered_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('items.id')
                        ->get();
        
        $assets = collect([]);

        if(count($results) != 0){
            foreach($results as $idx => $res){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_obat' => $res->nama_obat,
                    'supplier' => $res->supplier,
                    'jumlah_obat' => (int) $res->jumlah_obat,
                    'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                    'total_harga_raw' => (int) $res->total_harga
                ];
            }
    
            $jumlahObatSum = $assets->sum('jumlah_obat');
            $totalHargaSum = $assets->sum('total_harga_raw');
            
            $assets = $assets->sortByDesc('total_harga_raw');
    
            $assets[] = [
                'no' => 'Total Obat',
                'nama_obat' => $jumlahObatSum,
                'supplier' => 'Rp'.number_format((int) $totalHargaSum),
                'jumlah_obat' => '',
                'total_harga' => ''
            ];
        }

        return DataTables::of($assets)
            ->make();
    }

    public function listSupplier(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $others = Supplier::selectRaw('suppliers.name as nama_supplier')
                        ->selectRaw('COUNT(items.id) as sku_tersedia')
                        ->selectRaw('SUM(partner_item.stock_qty * items.price) as total_harga')
                        ->leftJoin('items', 'suppliers.id', '=', 'items.supplier_id')
                        ->leftJoin('partner_item', 'items.id', '=', 'partner_item.item_id')
                        ->where('stock_qty', '>', '0')
                        ->where('is_consigned', '=', '0')
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('partner_item.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('partner_item.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('suppliers.id', 'suppliers.name')
                        ->get();

        $jumlahSKU = Supplier::selectRaw('suppliers.name as nama_supplier')
                        ->selectRaw('COUNT(items.id) as jumlah_sku')    
                        ->leftJoin('items', 'items.supplier_id', '=', 'suppliers.id')
                        ->where(function($query) use($filterStart, $filterEnd){
                            if($filterStart != 'ALL'){
                                $query->where('items.created_at', '>=', $filterStart);
                            }

                            if($filterStart != 'ALL'){
                                $query->where('items.created_at', '<=', $filterEnd);
                            }
                        })
                        ->groupBy('suppliers.id')
                        ->get()
                        ->transform(function ($dt) use($others){
                            $isFind = false;

                            foreach($others as $other){
                                if($other->nama_supplier == $dt->nama_supplier){
                                    $isFind = true;

                                    $dt->sku_tersedia = $other->sku_tersedia;
                                    $dt->total_harga = $other->total_harga;
                                }
                            }

                            if(!$isFind){
                                $dt->sku_tersedia = 0;
                                $dt->total_harga = 0;
                            }

                            return $dt;
                        });

        $assets = collect([]);

        $suppliers = Supplier::all();

        foreach($suppliers as $idx => $supp){
            $find = false;

            foreach($jumlahSKU as $res){
                if($res->nama_supplier == $supp->name){
                    $find = true;

                    $assets[] = [
                            'no' => $idx+1,
                            'nama_supplier' => $res->nama_supplier,
                            'jumlah_sku' => (int) $res->jumlah_sku,
                            'sku_tersedia' => (int) $res->sku_tersedia,
                            'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                            'total_harga_raw' => (int) $res->total_harga
                    ];
                }
            }

            if(!$find){
                $assets[] = [
                    'no' => $idx+1,
                    'nama_supplier' => $supp->name,
                    'jumlah_sku' => 0,
                    'sku_tersedia' => 0,
                    'total_harga' => 'Rp'.number_format(0)
                ];
            }
        }

        $jumlahSKUSum = $assets->sum('jumlah_sku');
        $skuTersediaSum = $assets->sum('sku_tersedia');
        $jumlahObatSum = $assets->sum('total_harga_raw');

        $assets[] = [
            'no' => 'Total Supplier',
            'nama_supplier' => $jumlahSKUSum,
            'jumlah_sku' => $skuTersediaSum,
            'sku_tersedia' => 'Rp'.number_format((int) $jumlahObatSum),
            'total_harga' => ''
        ];

        return DataTables::of($assets)
            ->make();
    }
    
    public function listSOKlinikSchedule(Request $request){
        $filterStart = $request->filterStart;
        $filterEnd = $request->filterEnd;

        $getDue = SellOrder::selectRaw('CONCAT(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                            ->selectRaw('due_at `jadwal_so`')
                            ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                            ->join('partners', 'partners.id', '=', 'sell_orders.destination_partner_id')
                            ->where('status_id', '=', '2')
                            ->whereNotNull('due_at')
                            ->where('sell_orders.sell_order_type_id', 2)
                            ->where('status_kode', 'FIRST')
                            ->where(function($query) use($filterStart, $filterEnd){
                                if($filterStart != 'ALL'){
                                    $query->where('due_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('due_at', '<=', $filterEnd);
                                }
                            })
                            ->groupBy('sell_orders.id')
                            ->get()
                            ->transform(function ($dt){
                                $dt->due_at = Carbon::parse($dt->due_at)->format('d-m-Y');

                                return $dt;
                            });

        $results = SellOrder::selectRaw('CONCAT(partners.clinic_id, " - ", partners.name) `nama_klinik`')
                            ->selectRaw('SUM(sell_order_details.quantity) `jumlah_obat`')
                            ->selectRaw('SUM(sell_order_details.total) `total_harga`')
                            ->join('sell_order_details', 'sell_order_details.sell_order_id', '=', 'sell_orders.id')
                            ->join('partners', 'partners.id', '=', 'sell_orders.destination_partner_id')
                            ->where('status_id', '=', '2')
                            ->where('sell_orders.sell_order_type_id', 2)
                            ->where(function($query) use($filterStart, $filterEnd){
                                if($filterStart != 'ALL'){
                                    $query->where('sell_orders.created_at', '>=', $filterStart);
                                }

                                if($filterStart != 'ALL'){
                                    $query->where('sell_orders.created_at', '<=', $filterEnd);
                                }
                            })
                            ->groupBy('partners.id')
                            ->get();

        $assets = collect([]);

        if(count($getDue) != 0){
            foreach($getDue as $idx => $partner){
                $find = false;
    
                foreach($results as $res){
                    if($res->nama_klinik == $partner->nama_klinik){
                        $find = true;
    
                        $assets[] = [
                            'no' => $idx+1,
                            'nama_klinik' => $res->nama_klinik,
                            'jadwal_so' => $partner->jadwal_so,
                            'jumlah_obat' => (int) $res->jumlah_obat,
                            'total_harga' => 'Rp'.number_format((int) $res->total_harga),
                            'total_harga_raw' => (int) $res->total_harga
                        ];
                    }
                }
    
                if(!$find){
                    $assets[] = [
                        'no' => $idx+1,
                        'nama_klinik' => ($partner->clinic_id.' - '.$partner->name),
                        'jadwal_so' => '-',
                        'jumlah_obat' => 0,
                        'total_harga' => 'Rp'.number_format(0),
                        'total_harga_raw' => 0
                    ];
                }
            }
    
            $jumlahObatSum = $assets->sum('jumlah_obat');
            $totalHargaSum = $assets->sum('total_harga_raw');
            
            $assets = $assets->sortBy('jadwal_so');
    
            $assets[] = [
                'no' => 'Total SO',
                'nama_klinik' => $jumlahObatSum,
                'jadwal_so' => 'Rp'.number_format((int) $totalHargaSum),
                'jumlah_obat' => '',
                'total_harga' => ''
            ];
        }


        return DataTables::of($assets)
            ->make();
    }

    public function getAllSupplier(){
        $suppliers = Supplier::get()->transform(function ($dt){
            return [
                'id' => $dt->id,
                'text' => $dt->name
            ];
        })->toArray();

        array_unshift($suppliers, [
            'id' => 'ALL',
            'text' => 'ALL'
        ]);

        return response()->json([
            "results" => $suppliers
        ]);
    }

    public function getAllType(){
        $types = Type::get()->transform(function ($dt){
            return [
                'id' => $dt->id,
                'text' => $dt->name
            ];
        })->toArray();

        array_unshift($types, [
            'id' => 'ALL',
            'text' => 'ALL'
        ]);

        return response()->json([
            "results" => $types
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\AllBarcodeExport;
use App\Exports\AllHQStockExport;
use App\Exports\AllSKUExport;
use App\Exports\BarcodeAddExport;
use App\Exports\MitraStockExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ItemMasterRequest;
use App\Http\Requests\NewItemRequest;
use App\Models\LogError;
use App\Models\Item;
use App\Models\Type;
use App\Models\Supplier;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use App\Imports\ItemMasterImport;
use App\Imports\ItemStockImport;
use App\Models\Partner;
use App\Models\PartnerItem;
use App\Models\SellOrder;
use App\Models\Shelf;
use Carbon\Carbon;
use DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $dataItem = Item::with('supplier', 'type')
                            ->get()
                            ->transform(function($dt){
                                $dt->type_id = $dt->type->name;
                                $dt->supplier_id = $dt->supplier->name;
                                $dt->name = $dt->name .' ('.$dt->content.') ('.$dt->packaging.').';
                                $dt->price = 'Rp'.number_format($dt->price, 2);
                                return $dt;
                            });

            return DataTables::of($dataItem)
                ->addColumn('actions', function ($p) {
                    return [
                        // [
                        //     "route" => route('item.edit', ['item' => $p->id]),
                        //     "attr_id" => $p->id,
                        //     "icon" => 'fas fa-fw fa-edit',
                        //     "label" => 'Edit',
                        //     "btnStyle" => 'info'
                        // ],
                        [
                            "route" => route('item.destroy', ['item' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-trash',
                            "label" => 'Hapus',
                            "btnStyle" => 'danger'
                        ]
                    ];
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'item.index';

        return view('item.master.index', compact('route', 'success', 'error'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewItemRequest $request)
    {
        $file = $request->file('file');
        $result = [
            'type' => 'success',
            'message'   => 'Berhasil import master data obat!'
        ];
        DB::beginTransaction();
        try {
            Excel::import(new ItemMasterImport, $file);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type']    = 'error';
            $result['message'] = 'Gagal import master data obat, mohon coba kembali!';
        }

        return redirect()->route('item.index')->with($result['type'], $result['message']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Item $item)
    // {
    //     $types = Type::all();
    //     $suppliers = Supplier::all();
        
    //     return view('item.master.edit', compact('item', 'types', 'suppliers'));
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(ItemMasterRequest $request, Item $item): RedirectResponse
    // {
    //     $result = [
    //         'type' => 'success',
    //         'message'   => 'Berhasil mengubah master data obat!'
    //     ];
    //     $data = $request->all();

    //     DB::beginTransaction();
    //     try{
    //         $item->update($data);
    //         DB::commit();
    //     } catch(\Exception $e){
    //         DB::rollBack();
    //         LogError::insertLogError($e->getMessage());

    //         $result['type']    = 'error';
    //         $result['message'] = 'Gagal mengubah master data obat, mohon coba kembali!';
    //     }

    //     return redirect()->route('item.index')->with($result['type'], $result['message']);
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $name = $item->name;
        $result = [
            'type' => 'success',
            'message' => 'Berhasil menghapus master item '. $name .'!'
        ];

        DB::beginTransaction();
        try{
            $item->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type'] = 'error';
            $result['message'] = 'Gagal menghapus master data item, Mohon coba kembali!';

        }

        return redirect()->route('item.index')->with($result['type'], $result['message']);
    }

    public function indexStock(Request $request, string $type = null, string $stock = 'available'){
        if($request->ajax()){
            $items = PartnerItem::query();
            if($type && $type !== '' && $type !== 'sales'){
                $items = $items->where('is_consigned', ($type == 'hq' ? 0 : 1));
            }

            if($stock && $stock == 'available'){
                $items = $items->where('stock_qty', '>', 0);
            }
            $items = $items->get()
                            ->transform(function($dt) use ($type){
                                $stockOnProcess = DB::table('sell_order_details AS sod')
                                                                        ->join('sell_orders AS so', 'so.id', '=', 'sod.sell_order_id')
                                                                        ->where('sod.item_id', $dt->id)
                                                                        ->where('sod.quantity', '>', 0)
                                                                        ->where('so.status_id', 1)
                                                                        ->where('so.source_partner_id', $dt->partner->id)
                                                                        ->sum('sod.quantity');
                                $dt->sku = $dt->item->sku;
                                $dt->type_id = $dt->item->type->name;
                                $dt->supplier_id = $dt->item->supplier->name;
                                $dt->pabrik = $dt->item->manufacturer;
                                $dt->item_name = $dt->item->name .' ('.$dt->item->content.') ('.$dt->item->packaging.').';
                                $dt->unit = $dt->item->unit;
                                $dt->price = 'Rp'.number_format($dt->item->price, 2);
                                $dt->discounted = $dt->getRawOriginal('discount_price') == null
                                                                    ? '-'
                                                                    : 'Rp'.number_format($dt->discount_price, 2);
                                $dt->partner_name = $dt->partner->name;
                                $dt->shelf_id = $dt->shelf->name ?? '-';
                                $dt->stock_process = $stockOnProcess;
                                return $dt;
                            });
            
            return DataTables::of($items)
                ->editColumn('exp_date', function ($p) {
                    return [
                        'display' => date('d-m-Y', strtotime($p->exp_date)),
                        'timestamp' => date('Y-m-d', strtotime($p->exp_date))
                    ];
                })
                ->addColumn('actions', function ($p) use ($type) {
                    $returnedValue = [];

                    if($type && $type == 'sales'){
                        return $returnedValue;
                    }
                    
                    array_push($returnedValue, [
                        "route" => route('item.stock.edit', ['stock' => $p->id]),
                        "attr_id" => $p->id,
                        "icon" => 'fas fa-fw fa-edit',
                        "label" => 'Edit',
                        "btnStyle" => 'info'
                    ]);

                    // array_push($returnedValue, [
                    //     "route" => route('item.stock.print-barcode-each', ['stock' => $p->id]),
                    //     "attr_id" => $p->id,
                    //     "icon" => 'fas fa-fw fa-print',
                    //     "label" => 'Barcode',
                    //     "btnStyle" => 'success'
                    // ]);

                    array_push($returnedValue, [
                        "route" => route('item.stock.destroy', ['stock' => $p->id]),
                        "attr_id" => $p->id,
                        "icon" => 'fas fa-fw fa-trash',
                        "label" => 'Hapus',
                        "btnStyle" => 'danger'
                    ]);
                    
                    return $returnedValue;
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'item.stock.index';

        if($type == "hq"){
            $listNameMitra = Partner::select('name')->where('id', 1)->get();

            $listMitra = collect();

            foreach($listNameMitra as $mitra){
                $listMitra[$mitra->name] = $mitra->name;
            }
        }
        else{
            $listNameMitra = Partner::select('name')->where('id', '!=', 1)->get();

            $listMitra = collect();

            foreach($listNameMitra as $mitra){
                $listMitra[$mitra->name] = $mitra->name;
            }
        }

        return view('item.stock.index', compact('route','type','success', 'error', 'type', 'listMitra'));
    }

    public function importStock(NewItemRequest $request){
        $file = $request->file('file');
        $result = [
            'type' => 'success',
            'message'   => 'Berhasil import stock data obat!'
        ];
        DB::beginTransaction();
        try {
            Excel::import(new ItemStockImport, $file);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type']    = 'error';
            $result['message'] = $e->getMessage();
        }

        return redirect('/admin/item/stock/view/hq')->with($result['type'], $result['message']);
    }

    public function exportSKU(){
        $masterSKU = Item::all();

        return Excel::download(new AllSKUExport($masterSKU), 'Semua Item.xlsx');
    }

    public function exportStock(){
        $stockItems = PartnerItem::where('is_consigned', 0)->get();

        return Excel::download(new AllHQStockExport($stockItems), 'Semua Stock Gudang.xlsx');
    }

    public function exportStockMitra(Request $request){
        $fileTitle = '';

        $stockItems = PartnerItem::with('partner')->where('is_consigned', 1)->where('stock_qty', '>', 0);

        if($request->partnerId != 'ALL'){
            $partner = Partner::find($request->partnerId);

            $fileTitle = 'Stock Mitra '.$partner->clinic_id . ' - ' . $partner->name.'.xlsx';
            $stockItems = $stockItems->where('partner_id', $partner->id);
        }
        else{
            $fileTitle = 'Semua Stock Mitra.xlsx';
        }

        return Excel::download(new MitraStockExport($stockItems->get()->sortBy('partner.name')), $fileTitle);
    }

    public function createStock(Request $request)
    {
        if($request->ajax()){
            $search = $request->search ?? null;

            $items = Item::select('id', 'name', 'content', 'packaging', 'manufacturer')
                ->where(function ($query) use ($search) {
                    if(!is_null($search)){
                        $query->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('packaging', 'LIKE', '%'.$search.'%');
                    }
                })
                ->take(10)
                ->get()
                ->transform(function ($dt){
                    $dt->name = $dt->name .' ('.$dt->content.') ('.$dt->packaging.') ('.$dt->manufacturer.').';

                    return $dt;
                });

            $options = [];

            foreach ($items as $item){
                $itemShelf = PartnerItem::where('item_id', $item->id)->where('stock_qty', '>=', 0)->where('is_consigned', 0)->first();

                $options[] = [
                    'id' => $item->id,
                    'text' => $item->name,
                    'shelfId' => $itemShelf != null ? $itemShelf->shelf->id : null,
                    'shelfName' => $itemShelf != null ? $itemShelf->shelf->name : null
                ];
            }

            return response()->json($options);
        }

        $partners = Partner::orderBy('clinic_id')->where('id', 1)->get();
        $shelfs = Shelf::orderBy('name')->get();
        $route = 'item.stock.create';

        return view('item.stock.new', compact('partners', 'shelfs', 'route'));
    }

    public function getItemBatch(Request $request)
    {
        $itemId = $request->itemId;
        $partnerId = $request->partnerId;
        $search = $request->search ?? null;

        $partnerItems = PartnerItem::where(function ($query) use ($search) {
                            if(!is_null($search)){
                                $query->where('batch', $search);
                            }
                        })
                        ->where('partner_id', $partnerId)
                        ->where('item_id', $itemId)        
                        ->take(5)        
                        ->get();

        $returnedValue = [];

        foreach($partnerItems as $partnerItem){
            $alreadyExist = false;

            foreach($returnedValue as $val){
                if($val['id'] == $partnerItem->batch){
                    $alreadyExist = true;
                }
            }

            if(!$alreadyExist){
                array_push($returnedValue, [
                    'id' => $partnerItem->batch,
                    'text' => $partnerItem->batch
                ]);
            }
        }

        return response()->json([
            "results" => $returnedValue
        ]);
    }

    public function getShelfs(Request $request)
    {
        $search = $request->search ?? null;

        $shelfs = Shelf::where(function ($query) use ($search) {
                            if(!is_null($search) && $search != ''){
                                $query->where('name', 'LIKE', '%'.$search.'%');
                            }
                        })
                        ->take(5)        
                        ->get();

        $returnedValue = [];

        foreach($shelfs as $shelf){
            array_push($returnedValue, [
                'id' => $shelf->name,
                'text' => $shelf->name
            ]);
        }

        return response()->json([
            "results" => $returnedValue
        ]);
    }

    public function storeStock(Request $request){
        $rak = null;

        $result = [
            'type' => 'success',
            'message'   => 'Berhasil tambah stock obat!'
        ];

        DB::beginTransaction();
        try{
            if(isset($request->shelfId) && $request->shelfId != '-'){
                $rak = Shelf::where('name', $request->shelfId)->first();
                $rak = $rak ? $rak->id : $this->createNewShelf(strtoupper($request->shelfId));
            }
    
            $item = Item::where('id', $request->itemId)->first();
    
            $convertedExp = Carbon::parse($request->exp_date)->format('Y-m-d');
    
            $barcodeId = $this->getBarcodeAndUpdateStock($item->sku, $convertedExp, $request->batchId, $request->stock_qty, $rak);

            if($barcodeId['createNew'] == true){
                $item->partners()->attach($request->partnerId, [
                    'shelf_id' => $rak,
                    'barcode_id' => $barcodeId['id'],
                    'batch' => $request->batchId,
                    'exp_date' => $convertedExp,
                    'stock_qty' => $request->stock_qty,
                    'is_consigned' => $request->partnerId == 1 ? 0 : 1,
                    'discount_price' => $request->discount_price
                ]);

                if($request->partnerId != 1){
                    $isBarcodeIdExist = PartnerItem::where('barcode_id', $barcodeId['id'])->where('partner_id', 1)->count();

                    $konsinyasiFirstExist = SellOrder::where('destination_partner_id', $request->partnerId)->where('sell_order_type_id', 2)->where('status_id', 2)->where('status_kode', 'FIRST')->count();

                    if($isBarcodeIdExist == 0 || $konsinyasiFirstExist == 0){
                        DB::rollback();
                        $result['type']    = 'error';
                        $result['message'] = 'Gagal tambah stock obat, mohon buat terlebih dahulu item yang sama di stock pusat dan pastikan konsinyasi FIRST tersedia!';               
                        return redirect('/admin/item/stock/view/hq')->with($result['type'], $result['message']);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type']    = 'error';
            $result['message'] = 'Gagal tambah stock obat, mohon coba kembali!';
        }

        return redirect('/admin/item/stock/view/hq')->with($result['type'], $result['message']);
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

    public function getBarcode($sku, $exp, $batch, $qty, $rak, $isFailed = false){
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

    public function editStock($itemPartnerId){
        $data = PartnerItem::where('partner_item.id', $itemPartnerId)
                    ->get()
                    ->transform(function ($dt){
                        $item = Item::where('id', $dt->item_id)->first();
                        $partner = Partner::where('id', $dt->partner_id)->first();
                        $shelf = Shelf::where('id', $dt->shelf_id)->first();

                        return [
                            'id' => $dt->id,
                            'partner_id' => $dt->partner_id,
                            'item_id' => $dt->item_id,
                            'item_sku' => $item->sku,
                            'item_name' => $item->name .' ('.$item->content.') ('.$item->packaging.').',
                            'partner_clinic_id' => $partner->clinic_id,
                            'partner_name' => $partner->name,
                            'stock_qty' => $dt->stock_qty,
                            'discount_price' => $dt->getRawOriginal('discount_price'),
                            'batch' => $dt->batch,
                            'rak' => $shelf->id ?? '-',
                            'exp_date' => $dt->exp_date,
                            'is_consigned' => $dt->is_consigned
                        ];
                    })->first();

        $shelfs = Shelf::all();

        return view('item.stock.edit', compact('data', 'shelfs'));
    }

    public function updateStock(Request $request, $itemPartnerId){
        $stock = PartnerItem::where('partner_item.id', $itemPartnerId)->first();

        DB::beginTransaction();
        try{
            if($stock->shelf_id == $request->shelfId || !$request->has('shelfId')){
                PartnerItem::where('partner_item.id', $itemPartnerId)->update(['stock_qty' => $request->stock_qty, 'discount_price' => $request->discount_price]);
            }
            else{
                $shelfExist = PartnerItem::where('shelf_id', $request->shelfId)->where('barcode_id', $stock->barcode_id)->first();

                if($shelfExist){
                    PartnerItem::where('shelf_id', $request->shelfId)->where('barcode_id', $stock->barcode_id)->update(['stock_qty' => $shelfExist->stock_qty + $request->stock_qty, 'discount_price' => $request->discount_price]);
                    PartnerItem::where('partner_item.id', $itemPartnerId)->delete();
                }
                else{
                    PartnerItem::where('partner_item.id', $itemPartnerId)->update(['stock_qty' => $request->stock_qty, 'shelf_id' => $request->shelfId, 'discount_price' => $request->discount_price]);
                }
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah stock item, mohon coba kembali!';

            if($stock->partner_id == 1){
                return redirect('/admin/item/stock/view/hq')->with('error', $error);
            }
            else{
                return redirect('/admin/item/stock/view/partner')->with('error', $error);
            }
        }

        $success = 'Berhasil mengubah stock item!';

        if($stock->partner_id == 1){
            return redirect('/admin/item/stock/view/hq')->with('success', $success);
        }
        else{
            return redirect('/admin/item/stock/view/partner')->with('success', $success);
        }
    }

    public function destroyStock($itemPartnerId){
        $stock = PartnerItem::where('partner_item.id', $itemPartnerId)->first();

        DB::beginTransaction();
        try{
            PartnerItem::withoutGlobalScope('order')->where('partner_item.id', $itemPartnerId)->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus stock item, mohon coba kembali!';

            if($stock->partner_id == 1){
                return redirect('/admin/item/stock/view/hq')->with('error', $error);
            }
            else{
                return redirect('/admin/item/stock/view/partner')->with('error', $error);
            }
        }

        $success = 'Berhasil menghapus stock item !';

        if($stock->partner_id == 1){
            return redirect('/admin/item/stock/view/hq')->with('success', $success);
        }
        else{
            return redirect('/admin/item/stock/view/partner')->with('success', $success);
        }
    }

    public function printBarcode(){
        $partnerItems = PartnerItem::where('stock_qty', '>', '0')->where('partner_id', 1)->get();

        return Excel::download(new AllBarcodeExport($partnerItems), 'All Stock Barcode.xlsx');
    }

    public function printEachBarcode(Request $request){
        $partnerItems = PartnerItem::where('partner_item.id', $request->id)->get();
        $partnerItems[0]->stock_qty = $request->quantity;

        return Excel::download(new AllBarcodeExport($partnerItems), $partnerItems[0]->barcode_id . ' Stock Barcode.xlsx');
    }

    public function printAddBarcode(Request $request){
        $id_produk = $request->id_produk;
        $qty = $request->qty;
        $exp = $request->exp;
        $no_batch = $request->no_batch;
        $kode_rak = $request->kode_rak;

        $rak = Shelf::where('name', $kode_rak)->first();

        if(!$rak){
            $rak = Shelf::create([
                'name' => $kode_rak
            ]);
        }

        $item = Item::find($id_produk);
        $convertedExp = Carbon::parse($exp)->format('Y-m-d');

        $partnerItem = collect([
            'barcode_id' => $this->getBarcode($item->sku, $convertedExp, $no_batch, $qty, $rak->name)['id'],
            'name' => strtoupper($item->name . ' ' . getBerat($item->packaging) . ' - ' . $item->supplier->name),
            'stock_qty' => $qty,
            'exp' => $no_batch . '/' . Carbon::parse($exp)->format('m Y') . ' (' . $rak->name . ')'
        ]);

        return Excel::download(new BarcodeAddExport($partnerItem), $partnerItem['barcode_id'] . ' Stock Barcode.xlsx');
    }

    public function getPartner(Request $request){
        $search = $request->search ?? null;

        $partners = Partner::where(function ($query) use ($search) {
                            if(!is_null($search) && $search != ''){
                                $query->where('name', 'LIKE', '%'.$search.'%')
                                        ->orWhere('clinic_id', 'LIKE', '%'.$search.'%');
                            }
                        })
                        ->where('id', '!=', 1)
                        ->take(5)        
                        ->get();

        $returnedValue = [
            [
                'id' => 'ALL',
                'text' => 'All'
            ]
        ];

        foreach($partners as $partner){
            array_push($returnedValue, [
                'id' => $partner->id,
                'text' => $partner->clinic_id . ' - ' . $partner->name
            ]);
        }

        return response()->json([
            "results" => $returnedValue
        ]);
    }
}

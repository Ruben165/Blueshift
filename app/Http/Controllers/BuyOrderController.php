<?php

namespace App\Http\Controllers;

use App\Exports\AllBarcodeExport;
use App\Exports\BuyOrderExport;
use App\Exports\BuyOrderListExport;
use App\Imports\PBFItemsImport;
use App\Models\BuyOrder;
use App\Models\Item;
use App\Models\LogError;
use App\Models\Partner;
use App\Models\PartnerItem;
use App\Models\PBFImport;
use App\Models\Shelf;
use App\Models\Supplier;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DB;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class BuyOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $buyOrders = BuyOrder::orderBy('created_at', 'DESC')
                        ->get()
                        ->transform(function ($dt) {
                            $dt->supplier_id = $dt->supplier->name;
                            $dt->status_id = $dt->status->name;

                            return $dt;
                        });

            return DataTables::of($buyOrders)
                ->editColumn('created_at', function ($p) {
                    return [
                        'display' => date('d-m-Y H:i:s', strtotime($p->created_at)),
                        'timestamp' => date('Y-m-d H:i:s', strtotime($p->created_at))
                    ];
                })
                ->editColumn('SP_date', function ($p) {
                    if($p->SP_date){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->SP_date)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->SP_date))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }
                })
                ->editColumn('approve_date', function ($p) {
                    if($p->approve_date){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->approve_date)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->approve_date))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }
                })
                ->editColumn('send_date', function ($p) {
                    if($p->send_date){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->send_date)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->send_date))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }
                })
                ->editColumn('receive_date', function ($p) {
                    if($p->receive_date){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->receive_date)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->receive_date))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }
                })
                ->addColumn('actions', function ($p) {
                    $returnedValue = [];
                    
                    array_push($returnedValue, [
                        "route" => route('buy.show', ['buy' => $p->id]),
                        "attr_id" => $p->id,
                        "icon" => 'fas fa-fw fa-cube',
                        "label" => 'Detail',
                        "btnStyle" => 'primary'
                    ]);

                    if($p->status_id == 'Process'){
                        array_push($returnedValue, [
                            "route" => route('buy.edit', ['buy' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit',
                            "btnStyle" => 'info'
                        ]);
                        
                        array_push($returnedValue, [
                            "route" => route('buy.change-status', ['buy' => $p->id, 'status' => 3]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-trash',
                            "label" => 'Cancel',
                            "btnStyle" => 'danger'
                        ]);
                    }

                    return $returnedValue;
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'buy.index';
        $suppliers = Supplier::get()->toArray();
        $supplierList = [];

        foreach($suppliers as $supplier){
            $supplierList[] = [
                $supplier['name']
            ];
        }

        $supplierList = json_encode($supplierList);

        return view('buy.index', compact('route', 'success', 'error', 'supplierList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if($request->ajax()){
            $search = $request->search ?? null;

            $items = Item::where(function ($query) use ($search) {
                    if(!is_null($search)){
                        $query->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('packaging', 'LIKE', '%'.$search.'%');
                    }
                })
                ->where('supplier_id', $request->supplierId)
                ->where('type_id', $request->type)
                ->take(5)
                ->get()
                ->transform(function ($dt){
                    $dt->name = $dt->name .' ('.$dt->content.') ('.$dt->packaging.') ('.$dt->manufacturer.').';
                    $dt->type_id = $dt->type->name;

                    return $dt;
                });

            $options = [];

            foreach ($items as $item){
                $options[] = [
                    'id' => $item->id,
                    'text' => $item->name,
                    'itemValues' => $item
                ];
            }

            return response()->json(['results' => $options]);
        }

        $partners = Partner::all();
        $types = Type::all();

        $suppliers = Supplier::all();
        return view('buy.new', compact(['suppliers', 'partners', 'types']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        $items = json_decode($request->items);
        $fakturs = $request->faktur;
        $SPNo = $request->SPNo;
        $SPDate = $request->SPDate;
        $approveDate = $request->approveDate;
        $sendDate = $request->sendDate;
        $receiveDate = $request->receiveDate;
        $supplierId = $request->supplierId;
        $typeId = $request->type;
        // $path = $request->file('path');
        
        DB::beginTransaction();
        try{
            // Store Approval PDF
            // if($pdf = $path){
            //     $destPath = 'approval/';
            //     $fileName = date('YmdHis') . '-' . str_replace('/', '-', $documentNumber) . '.' . $pdf->getClientOriginalExtension();
            //     Storage::disk('public')->put($fileName, file_get_contents($pdf));
            //     Storage::disk('public')->move($fileName, 'approval/' . $fileName);
            //     $pathApproval = $destPath . $fileName;
            // }

            // Create Buy Orders
            $buyOrder = BuyOrder::create([
                'supplier_id' => $supplierId,
                'status_id' => 1,
                'type_id' => $typeId,
                'faktur' => $fakturs,
                'SP_no' => $SPNo,
                'SP_date' => $SPDate,
                'approve_date' => $approveDate,
                'send_date' => $sendDate,
                'receive_date' => $receiveDate,
                // 'path' => $pathApproval ?? null
            ]);

            foreach($items as $index => $item){
                $buyOrder->items()->attach($item->itemId, ['qty_request' => $item->quantityRequest, 'id_CRPOBR' => $item->idCRPOBR, 'clinic' => $item->clinic, 'qty_came' => $item->quantityCame, 'faktur' => $item->fakturItem == '' ? null : $item->fakturItem, 'batch' => $item->batch, 'expired' => $item->expired, 'shelf' => $item->shelf, 'HNA_each' => $item->HNAEach, 'discount' => $item->discount, 'buy_price' => $item->buyPrice, 'amount' => $item->amount, 'note' => $item->note, 'order' => $index]);
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage() . $e->getLine());

            $error = 'Gagal menambahkan Pembelian baru, tolong coba lagi!';

            return redirect()->route('buy.index')->with('error', $error);
        }

        $success = 'Berhasil menambahkan Pembelian baru!';

        return redirect()->route('buy.index')->with('success', $success);
    }

    public function edit(Request $request, BuyOrder $buy)
    {
        if($request->ajax()){
            $search = $request->search ?? null;

            $items = Item::where(function ($query) use ($search) {
                    if(!is_null($search)){
                        $query->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('packaging', 'LIKE', '%'.$search.'%');
                    }
                })
                ->where('supplier_id', $request->supplierId)
                ->where('type_id', $request->type)
                ->take(5)
                ->get()
                ->transform(function ($dt){
                    $dt->name = $dt->name .' ('.$dt->content.') ('.$dt->packaging.') ('.$dt->manufacturer.').';
                    $dt->type_id = $dt->type->name;

                    return $dt;
                });

            $options = [];

            foreach ($items as $item){
                $options[] = [
                    'id' => $item->id,
                    'text' => $item->name,
                    'itemValues' => $item
                ];
            }

            return response()->json(['results' => $options]);
        }

        $partners = Partner::all();
        $types = Type::all();

        $suppliers = Supplier::all();

        $buy->faktur = json_decode($buy->faktur);

        $listItems = collect();
        foreach($buy->items as $index => $item){
            $listItems->push(
                [
                    'id' => $item->pivot->order,
                    'itemId' => $item->id,
                    'name' => $item->name .' ('.$item->content.') ('.$item->packaging.') ('.$item->manufacturer.').',
                    'quantityRequest' => $item->pivot->qty_request,
                    'idCRPOBR' => $item->pivot->id_CRPOBR,
                    'clinic' => $item->pivot->clinic,
                    'quantityCame' => $item->pivot->qty_came,
                    'fakturItem' => $item->pivot->faktur,
                    'batch' => $item->pivot->batch,
                    'expired' => $item->pivot->expired,
                    'shelf' => $item->pivot->shelf,
                    'HNAEach' => $item->pivot->HNA_each,
                    'discount' => $item->pivot->discount,
                    'buyPrice' => $item->pivot->buy_price,
                    'amount' => $item->pivot->amount,
                    'note' => $item->pivot->note
                ]
            );
        }

        $listItems = json_decode($listItems->sortBy('id')->values());

        return view('buy.edit', compact(['suppliers', 'partners', 'types', 'buy', 'listItems']));
    }

    public function update(Request $request, BuyOrder $buy)
    {
        $items = json_decode($request->items);
        $fakturs = $request->faktur;
        $SPNo = $request->SPNo;
        $SPDate = $request->SPDate;
        $approveDate = $request->approveDate;
        $sendDate = $request->sendDate;
        $receiveDate = $request->receiveDate;
        $supplierId = $request->supplierId;
        $typeId = $request->type;
        // $path = $request->file('path');
        
        DB::beginTransaction();
        try{
            // Store Approval PDF
            // if($pdf = $path){
            //     $destPath = 'approval/';
            //     $fileName = date('YmdHis') . '-' . str_replace('/', '-', $documentNumber) . '.' . $pdf->getClientOriginalExtension();
            //     Storage::disk('public')->put($fileName, file_get_contents($pdf));
            //     Storage::disk('public')->move($fileName, 'approval/' . $fileName);
            //     $pathApproval = $destPath . $fileName;
            // }

            $buy->update([
                'supplier_id' => $supplierId,
                'status_id' => 1,
                'type_id' => $typeId,
                'faktur' => $fakturs,
                'SP_no' => $SPNo,
                'SP_date' => $SPDate,
                'approve_date' => $approveDate,
                'send_date' => $sendDate,
                'receive_date' => $receiveDate,
                // 'path' => $pathApproval ?? null
            ]);
            
            $buy->items()->detach();

            foreach($items as $index => $item){
                $buy->items()->attach($item->itemId, ['qty_request' => $item->quantityRequest, 'id_CRPOBR' => $item->idCRPOBR, 'clinic' => $item->clinic, 'qty_came' => $item->quantityCame, 'faktur' => $item->fakturItem == '' ? null : $item->fakturItem, 'batch' => $item->batch, 'expired' => $item->expired, 'shelf' => $item->shelf, 'HNA_each' => $item->HNAEach, 'discount' => $item->discount, 'buy_price' => $item->buyPrice, 'amount' => $item->amount, 'note' => $item->note, 'order' => $index]);
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage() . $e->getLine());

            $error = 'Gagal mengubah Pembelian, tolong coba lagi!';

            return redirect()->route('buy.index')->with('error', $error);
        }

        $success = 'Berhasil mengubah Pembelian!';

        return redirect()->route('buy.index')->with('success', $success);
    }

    /**
     * Display the specified resource.
     */
    public function show(BuyOrder $buy)
    {
        $buy->faktur = json_decode($buy->faktur);

        $listItems = collect();
        foreach($buy->items as $index => $item){
            $partnerItem = PartnerItem::find($item->pivot->partner_item_id);
            $barcodeId = null;

            if($partnerItem){
                $barcodeId = $partnerItem->barcode_id;
            }

            $listItems->push(
                [
                    'id' => $item->pivot->order,
                    'itemId' => $item->id,
                    'name' => $item->name .' ('.$item->content.') ('.$item->packaging.') ('.$item->manufacturer.').',
                    'quantityRequest' => $item->pivot->qty_request,
                    'idCRPOBR' => $item->pivot->id_CRPOBR,
                    'clinic' => $item->pivot->clinic,
                    'quantityCame' => $item->pivot->qty_came,
                    'fakturItem' => $item->pivot->faktur,
                    'batch' => $item->pivot->batch,
                    'expired' => $item->pivot->expired,
                    'shelf' => $item->pivot->shelf,
                    'HNAEach' => $item->pivot->HNA_each,
                    'discount' => $item->pivot->discount,
                    'buyPrice' => $item->pivot->buy_price,
                    'amount' => $item->pivot->amount,
                    'note' => $item->pivot->note,
                    'barcode_id' => $barcodeId
                ]
            );
        }

        $listItems = json_decode($listItems->sortBy('id')->values());

        $route = 'buy.detail';

        return view('buy.detail', compact('buy', 'listItems', 'route'));
    }

    public function exportExcel(BuyOrder $buy)
    {
        $items = $buy->items;
        return Excel::download(new BuyOrderExport($items), str_replace('/', '.', $buy->document_number) . '.xlsx');
    }

    public function exportPDF(BuyOrder $buy)
    {
        $pdf = new Mpdf([
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 18,
            'margin_right' => 18
        ]);

        $passedData = $buy;
        $date = Carbon::createFromFormat('Y-m-d', $passedData->tanggal_pembelian);

        App::setLocale('id');
        $translatedMonth = $date->translatedFormat('F');
        $passedData->tanggal_pembelian = $date->format('j') . ' ' . $translatedMonth . ' ' . $date->format('Y');

        $pdf->SetTitle(str_replace('/', '.', $buy->document_number) . '.pdf');
        
        $pdf->WriteHTML(view('buy.pdf', compact('passedData')));

        return $pdf->Output(str_replace('/', '.', $buy->document_number) . '.pdf', 'I');
    }

    public function printBarcode(BuyOrder $buy){
        $items = $buy->items->transform(function ($item){
            $item->order = $item->pivot->order;
            return $item; 
        })->filter(function ($item){
            return $item->pivot->partner_item_id != null;
        })->sortBy('order')->values();

        return Excel::download(new AllBarcodeExport($items), 'Pembelian ' . str_replace('/', '-', $buy->SP_no) . ' Barcode.xlsx');
    }

    public function terimaPesanan(BuyOrder $buy, Request $request){
        DB::beginTransaction();
        try{
            foreach($buy->items as $item){
                if($item->pivot->qty_came != 0){
                    $rak = Shelf::where('name', 'LIKE', $item->pivot->shelf)->first();         
                    $rak = $rak ? $rak->id : $this->createNewShelf(strtoupper($item->pivot->shelf));
    
                    $expired = $item->pivot->expired.'-01';
        
                    $barcodeId = $this->getBarcodeAndUpdateStock($item->sku, $expired, $item->pivot->batch, $item->pivot->qty_came, $rak);
                    
                    if($barcodeId['createNew'] == true){
                        $newItem = $item->partners()->attach(1, [
                            'shelf_id' => $rak,
                            'barcode_id' => $barcodeId['id'],
                            'batch' => $item->pivot->batch,
                            'exp_date' => $expired,
                            'stock_qty' => $item->pivot->qty_came,
                            'is_consigned' => 0
                        ]);
                    }
                    
                    $partnerItemId = PartnerItem::where('barcode_id', $barcodeId['id'])->where('shelf_id', $rak)->first()->id;
                    $item->pivot->partner_item_id = $partnerItemId;
                    $item->pivot->save();
                }
            }

            $buy->status_id = 2;
            $buy->save();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menyelesaikan pembelian, tolong coba lagi!';

            return redirect()->route('buy.index')->with('error', $error);
        }

        $success = 'Berhasil menyelesaikan pembelian, stock telah diupdate!';

        return redirect()->route('buy.index')->with('success', $success);
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

    public function changeStatus(BuyOrder $buy, $status)
    {   
        DB::beginTransaction();

        try{
            $buy->status_id = $status;
            $buy->update();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengganti status pembelian, tolong coba lagi!';

            return redirect()->route('buy.index')->with('error', $error);
        }

        $success = 'Berhasil mengganti status pembelian!';

        return redirect()->route('buy.index')->with('success', $success);
    }

    function convertToGreekNumber($number)
    {
        $greekNumbers = [
            '1' => 'I/',
            '2' => 'II/',
            '3' => 'III/',
            '4' => 'IV/',
            '5' => 'V/',
            '6' => 'VI/',
            '7' => 'VII/',
            '8' => 'VIII/',
            '9' => 'IX/',
            '10' => 'X/',
            '11' => 'XI/',
            '12' => 'XII/',
        ];

        return $greekNumbers[$number];
    }

    public function uploadItems(Request $request){
        if($request->hasFile('listPembelian')){
            // File Excel Validation
            $request->validate([
                'listPembelian' => 'mimes:xlsx'
            ]);

            $data = null;
            
            DB::beginTransaction();
            try{
                PBFImport::whereNotNull('id')->delete();
    
                Excel::import(new PBFItemsImport, $request->file('listPembelian'));

                $data = PBFImport::get();

                if(count($data) == 0){
                    DB::rollBack();

                    return response()->json([
                        'msg' => 'Terjadi kesalahan pada saat melakukan proses import, pastikan data yang dimasukkan sudah sesuai dengan format!'
                    ], 500);
                }
                
                $data = $data->transform(function($dt) use ($request){
                    $item = Item::where('sku', $dt->sku)->where('type_id', $request->type)->where('supplier_id', $request->supplierId)->first();

                    if(!$item){
                        return [
                            'notFound' => true
                        ];
                    }
                    else{
                        return [
                            'itemId' => $item->id,
                            'name' => $item->name .' ('.$item->content.') ('.$item->packaging.') ('.$item->manufacturer.').',
                            'quantityRequest' => $dt->qtyRequest,
                            'idCRPOBR' => $dt->idCRPOBR,
                            'clinic' => $dt->clinic,
                            'quantityCame' => $dt->qtyCame,
                            'fakturItem' => $dt->faktur,
                            'batch' => $dt->batch,
                            'expired' => $dt->expired,
                            'shelf' => $dt->shelf,
                            'HNAEach' => $dt->HNAEach,
                            'discount' => $dt->discount,
                            'buyPrice' => (int) ($dt->HNAEach * (100 - $dt->discount) / 100),
                            'amount' => (int) ($dt->HNAEach * (100 - $dt->discount) / 100) * (int) $dt->qtyCame,
                            'note' => $dt->note
                        ];
                    }
                });

                DB::commit();
            } catch(\Exception $e){
                DB::rollBack();
                LogError::insertLogError($e->getMessage());

                return response()->json([
                    'msg' => 'Terjadi kesalahan pada saat melakukan proses import!'
                ], 500);
            }

            return response()->json($data);
        }
    }

    public function exportListDetail(Request $request){
        $date = explode(' - ', $request->daterange);
        $startDate = Carbon::createFromFormat('d/m/Y', $date[0])->format('Y-m-d');
        $endDate = Carbon::createFromFormat('d/m/Y', $date[1])->format('Y-m-d');
        $typeName = '-Pembelian';

        $fileName = 'Export-List'. $typeName;

        $datetype = $request->datetype;

        $requestOrder = BuyOrder::with(['supplier',  'type', 'status', 'items'])
                                ->whereBetween($datetype, [$startDate, $endDate])
                                ->get();
                            
        return Excel::download(new BuyOrderListExport($requestOrder), $fileName . '.xlsx');
    }
}

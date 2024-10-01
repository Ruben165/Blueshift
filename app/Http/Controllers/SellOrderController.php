<?php

namespace App\Http\Controllers;

use App\Exports\ConsignmentPriceExport;
use App\Exports\ConsignmentRequestExport;
use App\Exports\ListKonsinyasiCSVExport;
use App\Exports\ListKonsinyasiExport;
use App\Exports\ListRequestOrderExport;
use App\Exports\ListSellOrderKonsinyasiExport;
use App\Exports\ListSellOrderRegulerExport;
use App\Exports\ListSellOrderReturExport;
use App\Exports\ListSellOrderSOExport;
use App\Exports\ListSellOrderTransferExport;
use App\Exports\ListSisaSOCSVExport;
use App\Exports\SellOrderExport;
use App\Exports\SellOrderKonsinyasiExport;
use App\Exports\SellOrderReturExport;
use App\Imports\SOImport;
use App\Models\ConsignmentRequest;
use App\Models\Group;
use App\Models\Item;
use App\Models\LogError;
use App\Models\Partner;
use App\Models\PartnerItem;
use App\Models\SellOrder;
use App\Models\SOImport as ModelsSOImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Gate;
use Illuminate\Support\Facades\Storage;

class SellOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $type = null)
    {
        if($request->ajax()){   
            $sellOrderTypeId = null;
            
            if($type == 'Reguler'){
                $sellOrderTypeId = [1];
            }
            else if($type == 'Konsinyasi'){
                $sellOrderTypeId = [2];
            }
            else if($type == 'Transfer'){
                $sellOrderTypeId = [3];
            }
            else if($type == 'Retur'){
                $sellOrderTypeId = [4];
            }
            else{
                $sellOrderTypeId = [5];
            }
            
            $sellOrders = SellOrder::orderBy('created_at', 'DESC')
                        ->whereIn('sell_order_type_id', $sellOrderTypeId)
                        ->orderBy('updated_at', 'DESC')
                        ->get()
                        ->transform(function ($dt) {
                            $dt->sell_order_type = $dt->sellOrderType->name;

                            if($dt->sell_order_type_id == 2){
                                if($dt->parent_id != null){
                                    $dt->sell_order_type_id = 'TR-' . $dt->status_kode;
                                }
                                else{
                                    $dt->sell_order_type_id = $dt->document_number;
                                }
                            }
                            else if(in_array($dt->sell_order_type_id, [1, 5] )){
                                $dt->sell_order_type_id = $dt->document_number;
                            }
                            else if($dt->sell_order_type_id == 4){
                                $dt->sell_order_type_id = $dt->document_number;
                                $dt->document_number = $dt->pic_retur;
                            }
                            else{
                                $dt->sell_order_type_id = $dt->sellOrderType->name;
                            }

                            $dt->batch_name =  ($dt->destinationPartner->groups()->first())->name ?? '-';

                            $dt->source_partner_id = $dt->sourcePartner->clinic_id . ' - ' . $dt->sourcePartner->name;
                            $dt->destination_partner_id = $dt->destinationPartner->clinic_id . ' - ' . $dt->destinationPartner->name;
                            $dt->status_id = $dt->status->name;
                            $dt->document_number = $dt->document_number == null ? '-' : $dt->document_number;

                            $dt->total_qty = DB::table('sell_order_details')
                                            ->select('quantity')
                                            ->whereNull('deleted_at')
                                            ->where('sell_order_id', $dt->id)
                                            ->sum('quantity');

                            $dt->total_price = 'Rp '.number_format($dt->total_price, 2);
                            $dt->due_at2 = $dt->due_at;

                            if($dt->status_id != 'Cancel'){
                                $dt->path = $dt->path == null ? 'Invoice Belum Dibuat' : 'Invoice Sudah Dibuat';
                            }
                            else{
                                $dt->path = '-';
                            }

                            return $dt;
                        });

            return DataTables::of($sellOrders)
                ->editColumn('created_at', function ($p) {
                    return [
                        'display' => date('d-m-Y H:i:s', strtotime($p->created_at)),
                        'timestamp' => date('Y-m-d H:i:s', strtotime($p->created_at))
                    ];
                })
                ->editColumn('delivered_at', function ($p) {
                    if($p->delivered_at != null){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->delivered_at)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->delivered_at))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }

                    return null;
                })
                ->editColumn('returned_at', function ($p) {
                    if($p->returned_at != null){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->returned_at)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->returned_at))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }

                    return null;
                })
                ->editColumn('returned_at', function ($p) {
                    if($p->returned_at != null){
                        return [
                            'display' => date('d-m-Y H:i:s', strtotime($p->returned_at)),
                            'timestamp' => date('Y-m-d H:i:s', strtotime($p->returned_at))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }

                    return null;
                })
                ->editColumn('due_at', function ($p) {
                    if($p->due_at != null){
                        return [
                            'display' => date('d-m-Y', strtotime($p->due_at)),
                            'timestamp' => date('Y-m-d', strtotime($p->due_at))
                        ];
                    }
                    else{
                        return [
                            'display' => '-',
                            'timestamp' => '-'
                        ];
                    }

                    return null;
                })
                ->addColumn('actions', function ($p) {
                    $returnedValue = [];
                    
                    array_push($returnedValue, [
                        "route" => route('sell.show', ['sell' => $p->id]),
                        "attr_id" => $p->id,
                        "icon" => 'fas fa-fw fa-cube',
                        "label" => 'Detail',
                        "btnStyle" => 'primary',
                        "btnClass" => 'Detail'
                    ]);
                    
                    if($p->status_id == 'Receipted' && $p->sell_order_type == 'Konsinyasi' && $p->status_kode == 'FIRST'){
                        // if($p->due_at2 < Carbon::now()){
                        //     array_push($returnedValue, [
                        //         "route" => route('sell.so', ['sell' => $p->id]),
                        //         "attr_id" => $p->id,
                        //         "icon" => 'fas fa-fw fa-receipt',
                        //         "label" => 'SO',
                        //         "btnStyle" => 'success',
                        //         "btnClass" => 'SO'
                        //     ]);
                        // }

                        array_push($returnedValue, [
                            "route" => '#',
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-calendar',
                            "label" => 'Jadwal SO',
                            "btnStyle" => 'info',
                            "btnClass" => 'Edit'
                        ]);
                    }

                    if($p->status_id != 'Cancel' && $p->sell_order_type == 'Konsinyasi'){
                        array_push($returnedValue, [
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-print',
                            "label" => 'Export',
                            "btnStyle" => 'success',
                            "btnClass" => 'Export',
                            "consingmentType" => $p->status_kode
                        ]);
                    }

                    if($p->status_id == 'Process'){
                        if(auth()->user()->hasPermissionTo('edit sell order')){
                            array_push($returnedValue, [
                                "route" => route('sell.edit', ['sell' => $p->id]),
                                "attr_id" => $p->id,
                                "icon" => 'fas fa-fw fa-edit',
                                "label" => 'Edit',
                                "btnStyle" => 'info',
                                "btnClass" => 'EditItem'
                            ]);
                        }
                        
                        array_push($returnedValue, [
                            "route" => route('sell.change-status', ['sell' => $p->id, 'status' => 3]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-trash',
                            "label" => 'Cancel',
                            "btnStyle" => 'danger',
                            "btnClass" => 'Cancel'
                        ]);
                    }

                    if($p->sell_order_type == 'Stock Opname'){
                        array_push($returnedValue, [
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-print',
                            "label" => 'Export Sisa',
                            "btnStyle" => 'success',
                            "btnClass" => 'ExportSisa',
                        ]);
                    }

                    return $returnedValue;
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'sell.index';

        $listNameMitra = Partner::select('name')->where('id', '!=', 1)->get();

        $listMitra = collect();

        foreach($listNameMitra as $mitra){
            $listMitra[$mitra->name] = $mitra->name;
        }

        $listNameBatch = Group::select('name')->get();

        $listBatch = collect();

        foreach($listNameBatch as $batch){
            $listBatch[$batch->name] = $batch->name;
        }

        $sourcePartners = [];

        if($type == 'Transfer' || $type == 'Retur'){
            $sourcePartners = Partner::where('id', '!=', 1)->get();
        }

        return view('sell.index', compact('route', 'success', 'error', 'type', 'listMitra', 'listBatch', 'sourcePartners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, string $type = null)
    {
        if($request->ajax()){
            $search = $request->search ?? null;
            $resultCount = 10;

            $items = PartnerItem::select('partner_item.id', 'barcode_id', 'item_id')
                ->with('item')
                ->where('partner_id', $request->sourcePartnerId)
                ->where(function ($query) use ($search) {
                    if($search != null){
                        $query->where('barcode_id', 'LIKE', '%' . $search . '%');
                    }
                })
                ->where('stock_qty', '>', 0)
                ->take($resultCount)
                ->get()
                ->transform(function ($dt){
                    $dt->item_name = $dt->item->name .' ('.$dt->item->content.') ('.$dt->item->packaging.') ('.$dt->item->manufacturer.') .';

                    return $dt;
                });

            $options = [];

            foreach ($items as $item){
                $options[] = [
                    'id' => $item->id,
                    'text' => $item->barcode_id . ' - ' . $item->item_name
                ];
            }

            return response()->json([
                'results' => $options,
            ]);
        }

        $items = PartnerItem::where('stock_qty', '>', 0);

        $type = $request->type;
        $partnerItems = null;

        if($type == 'Reguler' || $type == 'Konsinyasi'){
            $sourcePartners = Partner::where('id', 1)->get();
            $destinationPartners = Partner::where('id', '!=', 1)->get();

            $items = $items->where('partner_id', 1);
        }
        else if($type == 'Transfer'){
            $sourcePartners = Partner::where('id', $request->partnerSourceId)->get();
            $destinationPartners = Partner::whereNotIn('id', [1, $request->partnerSourceId])->get();

            $items = $items->where('partner_id', $request->partnerSourceId);
        }
        else{
            $sourcePartners = Partner::where('id', $request->partnerSourceId)->get();
            $destinationPartners = Partner::where('id', 1)->get();

            $items = $items->where('partner_id', $request->partnerSourceId);
        }

        $items = $items->get()->transform(function ($dt) use ($request) {
            $dt->item_name = $dt->item->name .' ('.$dt->item->content.') ('.$dt->item->packaging.') ('. $dt->item->manufacturer .') .';
            $dt->price = $dt->discount_price;
            $dt->shelfName = $dt->shelf->name ?? null;
            $dt->batchExp = $dt->batch . '-' . Carbon::createFromFormat('Y-m-d', $dt->exp_date)->format('m/y');
            
            $idSourcePartner = $request->partnerSourceId ?? 1;

            $listSellOrder = SellOrder::where('status_id', 1)->where('source_partner_id', $idSourcePartner)->get();

            foreach($listSellOrder as $sell){
                foreach($sell->partnerItems as $sellItem){
                    if($dt->id == $sellItem->id){
                        $dt->stock_qty -= $sellItem->pivot->quantity;
                    }
                }
            }

            return $dt;
        });

        return view('sell.new', compact(['sourcePartners', 'destinationPartners', 'items', 'type']));
    }

    public function edit(Request $request, SellOrder $sell = null){
        if($request->ajax()){
            $search = $request->search ?? null;
            $resultCount = 10;

            $items = PartnerItem::select('partner_item.id', 'barcode_id', 'item_id')
                ->with('item')
                ->where('partner_id', $request->sourcePartnerId)
                ->where(function ($query) use ($search) {
                    if($search != null){
                        $query->where('barcode_id', 'LIKE', '%' . $search . '%');
                    }
                })
                ->where('stock_qty', '>', 0)
                ->take($resultCount)
                ->get()
                ->transform(function ($dt){
                    $dt->item_name = $dt->item->name .' ('.$dt->item->content.') ('.$dt->item->packaging.') ('.$dt->item->manufacturer.') .';

                    return $dt;
                });

            $options = [];

            foreach ($items as $item){
                $options[] = [
                    'id' => $item->id,
                    'text' => $item->barcode_id . ' - ' . $item->item_name
                ];
            }

            return response()->json([
                'results' => $options,
            ]);
        }

        $items = PartnerItem::where('stock_qty', '>', 0)->where('partner_id', $sell->source_partner_id)->get()->transform(function ($dt) use ($sell) {
            $dt->item_name = $dt->item->name .' ('.$dt->item->content.') ('.$dt->item->packaging.').';
            $dt->price = $dt->discount_price;
            $dt->shelfName = $dt->shelf->name ?? null;
            $dt->batchExp = $dt->batch . '-' . Carbon::createFromFormat('Y-m-d', $dt->exp_date)->format('m/y');

            $listSellOrder = SellOrder::where('status_id', 1)->where('source_partner_id', $sell->source_partner_id)->where('id', '<>', $sell->id)->get();

            foreach($listSellOrder as $sell){
                foreach($sell->partnerItems as $sellItem){
                    if($dt->id == $sellItem->id){
                        $dt->stock_qty -= $sellItem->pivot->quantity;
                    }
                }
            }

            return $dt;
        });

        $type = $sell->sellOrderType->name;
        $type = ($type == 'Penjualan Reguler') ? 'Reguler' : $type;

        $sell->partnerItems->transform(function ($dt) use ($sell){
            $listSellOrder = SellOrder::where('status_id', 1)->where('id', '<>', $sell->id)->where('source_partner_id', $sell->source_partner_id)->get();

            foreach($listSellOrder as $sellOrder){
                foreach($sellOrder->partnerItems as $sellItem){
                    if($dt->id == $sellItem->id){
                        $dt->stock_qty -= $sellItem->pivot->quantity;
                    }
                }
            }

            $dt->item = $dt->item;

            return $dt;
        });

        return view('sell.edit', compact(['items', 'type', 'sell']));
    }

    public function update(Request $request, SellOrder $sell){
        $items = json_decode($request->items);
        $description = $request->description;
        $kodeKonsinyasi = $request->kodeKonsinyasi ?? $sell->status_kode;

        DB::beginTransaction();
        try{
            $sellOrderTypeId = null;
            $jenisPenjualan = $sell->sellOrderType->id;

            if($sell->sourcePartner->id == 1){
                $sellOrderTypeId = $jenisPenjualan;
                
                $idPengiriman = $request->idOrder;
                $sell->document_number = $idPengiriman;
                $sell->created_at = $request->created_at;
                $sell->delivered_at = $request->delivered_at;
            }
            else{
                if($sell->destinationPartner->id == 1){
                    $sellOrderTypeId = 4;

                    $surat_jalan_result = null;

                    if($request->file('surat_jalan_result')){
                        if($image = $request->file('surat_jalan_result')){
                            $destPath = 'bukti_retur_result/';
                            $fileName = date('YmdHis') . '-Hasil-Bukti-Retur.' . $image->getClientOriginalExtension();
                            Storage::disk('public')->put($fileName, file_get_contents($image));
                            Storage::disk('public')->move($fileName, 'bukti_retur_result/' . $fileName);

                            $surat_jalan_result = $destPath . $fileName;

                            if($sell->path_surat_jalan && Storage::disk('public')->exists($sell->path_surat_jalan)){
                                Storage::disk('public')->delete($sell->path_surat_jalan);
                            }
                        }
                    }
                }
                else{
                    $sellOrderTypeId = 3;
                }
            }

            if($jenisPenjualan == 2){    
                $sell->status_kode = $kodeKonsinyasi;
            }

            $sell->description = $description;

            $totalPrice = 0;

            // Detach all items
            $sell->partnerItems()->detach();

            foreach($items as $partnerItem){
                if($sell->sellOrderType->id == 4){
                    $sell->partnerItems()->attach($partnerItem->id, ['quantity' => $partnerItem->quantity, 'quantity_left' => $partnerItem->stock_qty - $partnerItem->quantity, 'total' => $partnerItem->quantity * (float) $partnerItem->price]);
                }
                else{
                    $sell->partnerItems()->attach($partnerItem->id, ['quantity' => $partnerItem->quantity, 'total' => $partnerItem->quantity * (float) $partnerItem->price]);
                }

                $totalPrice += $partnerItem->quantity * (float) $partnerItem->price;
            }

            $sell->total_price = $totalPrice;

            if($sell->sellOrderType->id == 4){
                $sell->returned_at = @$request->returDate;
                $sell->pic_retur = @$request->returPIC;
                
                if($surat_jalan_result){
                    $sell->path_surat_jalan = $surat_jalan_result;
                }
            }

            $sell->update();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            if($sellOrderTypeId == 1){
                $error = 'Gagal mengedit pengiriman reguler, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
            }
            else if($sellOrderTypeId == 2){
                $error = 'Gagal mengedit pengiriman, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
            }
            else if($sellOrderTypeId == 3){
                $error = 'Gagal mengedit transfer, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
            }
            else{
                $error = 'Gagal mengedit retur, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
            }
        }

        if($sellOrderTypeId == 1){
            $success = 'Berhasil mengedit pengiriman reguler!';
            return redirect()->route('sell.index', ['type' => 'Reguler'])->with('success', $success);
        }
        else if($sellOrderTypeId == 2){
            $success = 'Berhasil mengedit pengiriman konsinyasi!';
            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('success', $success);
        }
        else if($sellOrderTypeId == 3){
            $success = 'Berhasil mengedit transfer!';
            return redirect()->route('sell.index', ['type' => 'Transfer'])->with('success', $success);
        }
        else{
            $success = 'Berhasil mengedit retur!';
            return redirect()->route('sell.index', ['type' => 'Retur'])->with('success', $success);
        }
    }

    public function getFirstPartner(Request $request){
        if($request->ajax()){
            $search = $request->search ?? null;
            $isForDue = $request->forDue;

            $partner = [];

            if($search != null){
                $partners = Partner::select('id')->where('name', 'LIKE', '%' . $search . '%')->orWhere('clinic_id', 'LIKE', '%' . $search . '%')->get();
            }
            else{
                $partners = Partner::select('id')->get();
            }
            
            if($partners != null){
                foreach($partners as $p){
                    $partner[] = $p->id;
                }
            }

            if($isForDue == 'true'){
                $firstSellOrder = SellOrder::where('status_kode', 'FIRST')
                                            ->where('sell_order_type_id', 2)
                                            ->where('status_id', '!=', 3)
                                            ->where('due_at', '<=', Carbon::now())
                                            ->whereIn('destination_partner_id', $partner)
                                            ->take(5)
                                            ->get();
            }
            else{
                $firstSellOrder = SellOrder::where('status_kode', 'FIRST')
                                            ->where('sell_order_type_id', 2)
                                            ->where('status_id', '!=', 3)
                                            ->whereIn('destination_partner_id', $partner)
                                            ->take(5)
                                            ->get();
            }

            $options = [];
            
            foreach($firstSellOrder as $fs){
                $options[] = [
                    'id' => $fs->destination_partner_id,
                    'text' => $fs->destinationPartner->clinic_id . ' - ' . $fs->destinationPartner->name
                ];
            }

            return response()->json([
                'results' => $options,
            ]);
        }
    }

    public function getConsignCode(Request $request){
        if($request->ajax()){
            $partnerId = $request->destinationPartnerId;

            $isFirstExist = SellOrder::where('destination_partner_id', $partnerId)->where('sell_order_type_id', 2)->where('status_id', '!=', 3)->where('status_kode', 'FIRST')->first();

            $options = [];

            if(!$isFirstExist){
                $options[] = [
                    'id' => 'FIRST',
                    'text' => 'First'
                ];
            }

            $options[] = [
                'id' => 'RO',
                'text' => 'Repeat Order'
            ];

            $options[] = [
                'id' => 'AF',
                'text' => 'Auto Fill'
            ];

            return response()->json([
                'results' => $options,
            ]);
        }
    }

    public function createPermintaan(Request $request, $type)
    {
        if($request->ajax()){
            $search = $request->search ?? null;

            $items = Item::where(function ($query) use ($search) {
                    if(!is_null($search)){
                        $query->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('packaging', 'LIKE', '%'.$search.'%');
                    }
                })
                ->take(10)
                ->get()
                ->transform(function ($dt){
                    $dt->name = $dt->name .' ('.$dt->content.') ('.$dt->packaging.') ('.$dt->manufacturer.') .';
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

        $partners = Partner::where('id', '!=', 1)->get();

        $route = 'sell.permintaan.create';
        $routeSearch = 'item.stock.index';

        return view('sell.permintaan.new', compact(['partners', 'route', 'routeSearch', 'type']));
    }

    public function getPartnerItems(Request $request){
        if($request->ajax()){
            $items = PartnerItem::withoutGlobalScope('order')
                ->select('item_id')
                ->where('partner_id', $request->destinationPartnerId)
                ->groupBy('item_id')
                ->get();

            $itemIds = [];

            foreach($items as $item){
                array_push($itemIds, $item->item_id);
            }

            return response()->json([
                'results' => $itemIds,
            ]);
        }

        return response()->json([
            'results' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $items = json_decode($request->items);
        $partnerSourceId = $request->partnerSourceId ?? 1;
        $partnerDestinationId = $request->partnerDestinationId;
        $description = $request->description;
        $kodeKonsinyasi = $request->kodeKonsinyasi ?? null;

        if($partnerSourceId == 1){
            $jenisPenjualan = $request->jenisPenjualan;
        }

        DB::beginTransaction();
        try{
            $sellOrderTypeId = null;

            if($partnerSourceId == 1){
                $sellOrderTypeId = $jenisPenjualan;

                if($sellOrderTypeId == 1){
                    // $kodeKonsinyasi = $request->idOrder;

                    // $latestRegulerKonsinyasi = SellOrder::whereIn('sell_order_type_id', [1, 2])->whereMonth('created_at', Carbon::now()->format('m'))->whereYear('created_at', Carbon::now()->year)->orderBy('created_at', 'DESC')->first();
                    // $orderNo = null;

                    // if($latestRegulerKonsinyasi != null){
                    //     $orderNo = ((int) explode('/', $latestRegulerKonsinyasi->document_number)[0]) + 1;
                    // }
                    // else{
                    //     $orderNo = 1;
                    // }

                    // $documentNumber = sprintf("%04d", $orderNo) . '/S18-REG/MK/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');
                    $documentNumber = $request->idOrder;
                }
                else{
                    $firstAlreadyExist = SellOrder::where('status_id', 2)
                                                    ->where('sell_order_type_id', 2)
                                                    ->where('destination_partner_id', $partnerDestinationId)
                                                    ->where('status_kode', 'FIRST')
                                                    ->count();

                    if($kodeKonsinyasi == 'FIRST'){
                        if($firstAlreadyExist > 0){
                            DB::rollBack();

                            $error = 'Gagal menambahkan konsinyasi baru, mitra konsinyasi sudah memiliki konsinyasi pertama!';
                            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
                        }
                    }
                    else{
                        if($firstAlreadyExist == 0){
                            DB::rollBack();

                            $error = 'Gagal menambahkan konsinyasi baru, mitra konsinyasi belum memiliki konsinyasi pertama!';
                            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
                        }
                    }

                    if($kodeKonsinyasi == 'RO'){
                        $latestRO = SellOrder::whereIn('status_id', [1, 2])
                                                ->where('sell_order_type_id', 2)
                                                ->where('destination_partner_id', $partnerDestinationId)
                                                ->where('status_kode', 'LIKE', 'RO%')
                                                ->orderBy('delivered_at', 'DESC')
                                                ->first();

                        // Check if FIRST has RO description
                        $first = SellOrder::where('status_id', 2)
                        ->where('sell_order_type_id', 2)
                        ->where('destination_partner_id', $partnerDestinationId)
                        ->where('status_kode', 'FIRST')->first();

                        if($latestRO != null){
                            $newNumberRO = ((int) substr($latestRO->status_kode, 2)) + 1;
                            $kodeKonsinyasi = 'RO' . $newNumberRO;
                        }
                        else if(str_contains($first->description, '[RO')){
                            $newNumberRO = explode(']', explode('[RO', $first->description)[1])[0] + 1; 
                            $kodeKonsinyasi = 'RO' . $newNumberRO;
                        }
                        else{
                            $kodeKonsinyasi = 'RO1';
                        }
                    }

                    // $latestRegulerKonsinyasi = SellOrder::whereIn('sell_order_type_id', [1, 2])->whereMonth('created_at', Carbon::now()->format('m'))->whereYear('created_at', Carbon::now()->year)->orderBy('created_at', 'DESC')->first();
                    // $orderNo = null;
    
                    // if($latestRegulerKonsinyasi != null){
                    //     $orderNo = ((int) explode('/', $latestRegulerKonsinyasi->document_number)[0]) + 1;
                    // }
                    // else{
                    //     $orderNo = 1;
                    // }
    
                    // $documentNumber = sprintf("%04d", $orderNo) . '/S18-REG/MK/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');
                    $documentNumber = $request->idOrder;
                }
            }
            else{
                if($partnerDestinationId == 1){
                    $sellOrderTypeId = 4;

                    $latestRetur = SellOrder::where('sell_order_type_id', 4)->where('status_id', '!=', 3)->orderBy('created_at', 'DESC')->first();
                    $orderNo = null;
    
                    if($latestRetur != null){
                        $orderNo = ((int) explode('/', $latestRetur->document_number)[0]) + 1;
                    }
                    else{
                        $orderNo = 1;
                    }
                    
                    $documentNumber = sprintf("%03d", $orderNo) . '/S18-RR/SMI/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');

                    $surat_jalan_result = null;

                    if($request->file('surat_jalan_result')){
                        if($image = $request->file('surat_jalan_result')){
                            $destPath = 'bukti_retur_result/';
                            $fileName = date('YmdHis') . '-Hasil-Bukti-Retur.' . $image->getClientOriginalExtension();
                            Storage::disk('public')->put($fileName, file_get_contents($image));
                            Storage::disk('public')->move($fileName, 'bukti_retur_result/' . $fileName);

                            $surat_jalan_result = $destPath . $fileName;
                        }
                    }
                }
                else{
                    $sellOrderTypeId = 3;

                    // Document untuk transfer
                    $latestTransfer = SellOrder::where('sell_order_type_id', 3)->whereMonth('created_at', Carbon::now()->format('m'))->whereYear('created_at', Carbon::now()->year)->orderBy('created_at', 'DESC')->first();
                    $orderNo = null;

                    if($latestTransfer != null){
                        $orderNo = ((int) explode('/', $latestTransfer->document_number)[0]) + 1;
                    }
                    else{
                        $orderNo = 1;
                    }

                    $documentNumber = sprintf("%04d", $orderNo) . '/S18-TRA/SMI/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');
                }
            }

            $sellOrder = SellOrder::create([
                'sell_order_type_id' => $sellOrderTypeId,
                'source_partner_id' => $partnerSourceId,
                'destination_partner_id' => $partnerDestinationId,
                'status_id' => 1,
                'status_kode' => $kodeKonsinyasi,
                'total_price' => 0,
                'description' => $description,
                'document_number' => $documentNumber ?? null,
            ]);

            $totalPrice = 0;

            foreach($items as $partnerItem){
                if($sellOrder->sellOrderType->id == 4){
                    $sellOrder->partnerItems()->attach($partnerItem->id, ['quantity' => $partnerItem->quantity, 'quantity_left' => $partnerItem->stock_qty - $partnerItem->quantity, 'total' => $partnerItem->quantity * (float) $partnerItem->price]);
                }
                else{
                    $sellOrder->partnerItems()->attach($partnerItem->id, ['quantity' => $partnerItem->quantity, 'total' => $partnerItem->quantity * (float) $partnerItem->price]);
                }
                
                $totalPrice += $partnerItem->quantity * (float) $partnerItem->price;
            }

            if($sellOrder->sellOrderType->id == 4){
                $sellOrder->returned_at = @$request->returDate;
                $sellOrder->pic_retur = @$request->returPIC;
                $sellOrder->path_surat_jalan = @$surat_jalan_result;
            }

            $sellOrder->total_price = $totalPrice;
            $sellOrder->update();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            if($sellOrderTypeId == 1){
                $error = 'Gagal menambahkan pengiriman reguler baru, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
            }
            else if($sellOrderTypeId == 2){
                $error = 'Gagal menambahkan pengiriman baru, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
            }
            else if($sellOrderTypeId == 3){
                $error = 'Gagal menambahkan transfer baru, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
            }
            else{
                $error = 'Gagal menambahkan retur baru, tolong coba lagi!';
                return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
            }
        }

        if($sellOrderTypeId == 1){
            $success = 'Berhasil menambahkan pengiriman reguler baru!';
            return redirect()->route('sell.index', ['type' => 'Reguler'])->with('success', $success);
        }
        else if($sellOrderTypeId == 2){
            $success = 'Berhasil menambahkan pengiriman baru!';
            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('success', $success);
        }
        else if($sellOrderTypeId == 3){
            $success = 'Berhasil menambahkan transfer baru!';
            return redirect()->route('sell.index', ['type' => 'Transfer'])->with('success', $success);
        }
        else{
            $success = 'Berhasil menambahkan retur baru!';
            return redirect()->route('sell.index', ['type' => 'Retur'])->with('success', $success);
        }
    }

    public function changeStatus(SellOrder $sell, $status, Request $request)
    {   
        DB::beginTransaction();

        $sellOrderTypeId = $sell->sellOrderType->id;

        try{
            $sell->status_id = $status;

            if($sell->due_at != null){
                $sell->due_at = null;
            }

            if(isset($request->picCancel) && isset($request->alasan)){
                $sell->pic_cancel = $request->picCancel;
                $sell->alasan_cancel = $request->alasan;
                $sell->document_number = '-';
                if($sell->sell_order_type_id == 1){
                    $sell->status_kode = '-';
                }

                if($request->file('buktiCancel')){
                    if($image = $request->file('buktiCancel')){
                        $destPath = 'bukti_cancel_pengiriman/';
                        $fileName = date('YmdHis') . '-Bukti-Cancel-Pengiriman.' . $image->getClientOriginalExtension();
                        Storage::disk('public')->put($fileName, file_get_contents($image));
                        Storage::disk('public')->move($fileName, 'bukti_cancel_pengiriman/' . $fileName);
                        $path_cancel = $destPath . $fileName;
                    }
    
                    $sell->path_cancel = $path_cancel;
                }
            }

            $sell->update();

            // if($sell->sellOrderType->name == 'Konsinyasi'){
            //     $consignmentRequest = ConsignmentRequest::where('sell_order_id', $sell->id)->first();

            //     if($consignmentRequest){
            //         $consignmentRequest->processed_at = NULL;
            //         $consignmentRequest->status_id = 5;
            //         $consignmentRequest->save();
            //     }
            // }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengganti status pengiriman, tolong coba lagi!';

            if($sellOrderTypeId == 1 || $sellOrderTypeId == 5){
                return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
            }
            else if($sellOrderTypeId == 2){
                return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
            }
            else if($sellOrderTypeId == 3){
                return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
            }
            else{
                return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
            }
        }

        $success = 'Berhasil mengganti status pengiriman!';

        if($sellOrderTypeId == 1 || $sellOrderTypeId == 5){
            return redirect()->route('sell.index', ['type' => 'Reguler'])->with('success', $success);
        }
        else if($sellOrderTypeId == 2){
            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('success', $success);
        }
        else if($sellOrderTypeId == 3){
            return redirect()->route('sell.index', ['type' => 'Transfer'])->with('success', $success);
        }
        else{
            return redirect()->route('sell.index', ['type' => 'Retur'])->with('success', $success);
        }
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

    /**
     * Display the specified resource.
     */
    public function show(SellOrder $sell)
    {
        $success = session('success') ?? null;
        $error = session('error') ?? null;

        if($sell->sellOrderType->name == 'Konsinyasi' && $sell->status_kode != 'FIRST'){
            $sell->due_at = SellOrder::select('due_at')->where('status_kode', 'FIRST')->where('destination_partner_id', $sell->destination_partner_id)->pluck('due_at')[0];
        }

        $sell->partnerItems->transform(function ($dt) use ($sell){
            $listSellOrder = SellOrder::where('status_id', 1)->where('id', '<>', $sell->id)->where('source_partner_id', $sell->source_partner_id)->get();

            foreach($listSellOrder as $sellOrder){
                foreach($sellOrder->partnerItems as $sellItem){
                    if($dt->id == $sellItem->id){
                        $dt->stock_qty -= $sellItem->pivot->quantity;
                    }
                }
            }

            return $dt;
        });

        $items = null;

        if($sell->sellOrderType->name == 'Retur'){
            $items = PartnerItem::where('stock_qty', '>', 0)->where('partner_id', $sell->source_partner_id)->get()->transform(function ($dt) use ($sell) {
                $dt->item_name = $dt->item->name .' ('.$dt->item->content.') ('.$dt->item->packaging.').';
                $dt->price = $dt->item->price;
                $dt->shelfName = $dt->shelf->name ?? null;
                $dt->batchExp = $dt->batch . '-' . Carbon::createFromFormat('Y-m-d', $dt->exp_date)->format('m/y');
    
                $listSellOrder = SellOrder::where('status_id', 1)->where('source_partner_id', $sell->source_partner_id)->where('id', '<>', $sell->id)->get();
    
                foreach($listSellOrder as $sell){
                    foreach($sell->partnerItems as $sellItem){
                        if($dt->id == $sellItem->id){
                            $dt->stock_qty -= $sellItem->pivot->quantity;
                        }
                    }
                }
    
                return $dt;
            });
        }

        $route = 'sell.detail';

        return view('sell.detail', compact('sell', 'success', 'error', 'items', 'route'));
    }

    public function showPermintaan(ConsignmentRequest $consignmentRequest, $type)
    {
        $route = 'sell.permintaan.show';
        $consignmentRequestAdditionalData = null;

        foreach($consignmentRequest->items as $idx => $item){
            if($idx == 0){
                $consignmentRequestAdditionalData = [
                    'sender_id' => $item->pivot->sender_id ?? null,
                    'sender_pic' => $item->pivot->sender_pic ?? null,
                    'request_date' => $item->pivot->request_date ?? null,
                    'deliver_date' => $item->pivot->deliver_date ?? null
                ];
            }

            break;
        }

        return view('sell.permintaan.detail', compact('consignmentRequest', 'route', 'type', 'consignmentRequestAdditionalData'));
    }

    public function exportExcel(SellOrder $sell)
    {
        $items = $sell->partnerItems;

        if($sell->sell_order_type_id == 5){
            return Excel::download(new SellOrderKonsinyasiExport($items), str_replace('/', '.', $sell->document_number) . '.xlsx');
        }
        else if(in_array($sell->sell_order_type_id, [1, 2, 3])){
            return Excel::download(new SellOrderExport($items), str_replace('/', '.', $sell->document_number) . '.xlsx');
        }
        else{
            return Excel::download(new SellOrderReturExport($sell), str_replace('/', '.', $sell->document_number) . '.xlsx');
        }
    }

    public function calculateIterations($total) {
        $returnValue = 1;
        
        while ($total > 25) {
            $total -= 25;
            
            $returnValue++;
        }
        
        return $returnValue;
    }

    public function exportPDF(SellOrder $sell)
    {
        $passedData = $sell;

        Carbon::setLocale('id');

        $dateUsed = in_array($passedData->sell_order_type_id, [1, 2]) ? ($sell->delivered_at ? $sell->delivered_at : Carbon::now()) : $sell->created_at;

        $passedData->formated_created_at = Carbon::parse($dateUsed)->translatedFormat('d F Y');

        if($sell->due_at != null){
            $passedData->formated_due_at = Carbon::parse($sell->due_at)->translatedFormat('d F Y');
        }
        
        if(in_array($passedData->sell_order_type_id, [1, 2, 3])){
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 5,
                'margin_right' => 5,
                'format' => 'A5'
            ]);

            $pdf->SetTitle(str_replace('/', '.', $sell->document_number) . '.pdf');
            $totalList = count($passedData->partnerItems);

            $isLast = 0;
            $startNo = 0;
            $lastNo = 24;
            $totalPage = $this->calculateIterations($totalList);
            $currentPage = 1;

            if($totalPage != 1){
                while($currentPage < $totalPage){
                    if($currentPage == $totalPage){
                        $isLast = 1;
                    }
    
                    $pdf->WriteHTML(view('sell.pdf', compact('passedData', 'isLast', 'startNo', 'lastNo', 'currentPage', 'totalPage')));
    
                    if($isLast == 0){
                        $pdf->addPage();
                        $startNo += 25;
                        $lastNo += 25;
                    }
    
                    $currentPage++;
                }
            }

            $isLast = 1;
            $pdf->WriteHTML(view('sell.pdf', compact('passedData', 'isLast', 'startNo', 'lastNo', 'currentPage', 'totalPage')));
        }
        else if($sell->sell_order_type_id == 5){
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'margin_right' => 18,
            ]);

            $pdf->SetTitle(str_replace('/', '.', $sell->document_number) . '.pdf');

            $pdf->WriteHTML(view('sell.pdfKonsinyasi', compact('passedData')));
        }
        else{
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'margin_right' => 18,
            ]);

            $pdf->SetTitle(str_replace('/', '.', $sell->document_number) . '.pdf');

            $pdf->WriteHTML(view('sell.pdfRetur', compact('passedData')));
        }

        return $pdf->Output(str_replace('/', '.', $sell->document_number) . '.pdf', 'I');
    }

    public function exportPDFInvoiceReguler(SellOrder $sell, Request $request)
    {
        $invoiceInfo = [
            'tanggal' => Carbon::now()->format('d/m/Y'),
            'noTagihan' => $request->noTagihan,
            'customerId' => $request->customerId,
            'namaSales' => $request->namaSales,
            'expiredDate' => Carbon::parse($request->expiredDate)->format('d/m/Y'),
            'notes' => preg_split('/\r\n|\r|\n/', $request->note),
            'payment' => preg_split('/\r\n|\r|\n/', $request->payment)
        ];

        $passedData = clone $sell;

        DB::beginTransaction();
        try{
            Carbon::setLocale('id');
            $passedData->formated_created_at = Carbon::parse($sell->created_at)->translatedFormat('d F Y');
    
            if($sell->due_at != null){
                $passedData->formated_due_at = Carbon::parse($sell->due_at)->translatedFormat('d F Y');
            }
    
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'margin_right' => 18,
            ]);
    
            $pdf->SetTitle(str_replace('/', '.', $sell->document_number) . '.pdf');
    
            $pdf->WriteHTML(view('sell.pdfReguler', compact('passedData', 'invoiceInfo')));
    
            $pdfFilePath = 'invoice/' . str_replace('/', '.', $sell->document_number) . '.pdf';
            $pdfContent = $pdf->Output(str_replace('/', '.', $sell->document_number) . '.pdf', 'S');
    
            $sell->path = $pdfFilePath;
            $sell->save();
    
            Storage::disk('public')->put($pdfFilePath, $pdfContent);

            DB::commit();
            
            return $pdf->Output(str_replace('/', '.', $sell->document_number) . '.pdf', 'I');
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            echo 'Gagal mengexport invoice, silahkan coba lagi!';
        }

    }

    public function exportHarga(Request $request){
        $isAll = $request->allOrNot == 'All' ? true : false;
        $isPDF = $request->jenisExport == 'PDF' ? true : false;

        $sellOrder = SellOrder::find($request->id);

        $priceList = collect([
            'Umum' => collect(),
            'Khusus' => collect(),
            'Prekursor' => collect(),
            'Alkes' => collect(),
            'Obat Obat Tertentu' => collect(),
        ]);

        $totalType = [
            'Umum' => 0,
            'Khusus' => 0,
            'Prekursor' => 0,
            'Alkes' => 0,
            'Obat Obat Tertentu' => 0,
        ];

        $priceType = [
            'Umum' => 0,
            'Khusus' => 0,
            'Prekursor' => 0,
            'Alkes' => 0,
            'Obat Obat Tertentu' => 0,
        ];

        if($isAll){
            $partnerItems = PartnerItem::where('stock_qty', '>', 0)->where('partner_id', $sellOrder->destination_partner_id)->get();
            
            $sellOrderStillProcess = SellOrder::where('status_id', 1)->where('destination_partner_id', $sellOrder->destination_partner_id)->get();

            foreach($sellOrderStillProcess as $sellOrderProcess){
                foreach($sellOrderProcess->partnerItems as $sellOrderDetail){
                    $partnerItems->add($sellOrderDetail);
                }
            }
        }
        else{
            $partnerItems = $sellOrder->partnerItems;
        }
        
        foreach($partnerItems as $item){
            if(count($priceList[$item->item->type->name]->where('itemId', $item->item_id)) == 0){
                $priceList[$item->item->type->name]->add([
                    'itemId' => $item->item_id,
                    'stock_qty' => (float) ($item->pivot ? $item->pivot->quantity : $item->stock_qty),
                    'price' => (float) $item->discount_price,
                    'name' => strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')')
                ]);
            }
            else{
                $priceList[$item->item->type->name]->transform(function ($price) use ($item) {
                    if ($price['itemId'] === $item->item_id) {
                        $price['stock_qty'] += (float) ($item->pivot ? $item->pivot->quantity : $item->stock_qty);
                    }
                    return $price;
                });
            }
            
            $totalType[$item->item->type->name] += (float) ($item->pivot ? $item->pivot->quantity : $item->stock_qty);
            $priceType[$item->item->type->name] += (float) ($item->pivot ? $item->pivot->quantity : $item->stock_qty) * $item->discount_price;
        }

        if($isPDF){
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'margin_right' => 18
            ]);
    
            
            $prices = $priceList;
            $totalSum = $totalType;
            $typeExport = 'pdf';
            
            $pdf->AddPage('L');
            $pdf->SetTitle('List Harga Konsinyasi ' . $sellOrder->destinationPartner->name . ' - ' . ($isAll ? 'All' : $sellOrder->status_kode) . '.pdf');
            $pdf->WriteHTML(view('sell.konsinyasi.export', compact('prices', 'totalSum', 'sellOrder', 'isAll', 'priceType', 'typeExport')));
    
            return $pdf->Output('List Harga Konsinyasi ' . $sellOrder->destinationPartner->name . ' - ' . ($isAll ? 'All' : $sellOrder->status_kode) . '.pdf', 'I');
        }
        else{
            return Excel::download(new ConsignmentPriceExport($priceList, $totalType, $sellOrder, $isAll, $priceType), 'List Harga Konsinyasi ' . $sellOrder->destinationPartner->name . ' - ' . ($isAll ? 'All' : $sellOrder->status_kode) . '.xlsx');
        }
    }

    public function exportSisaStock(Request $request){
        $partnerId = $request->partnerIdPrint;
        $type = $request->jenisExport;

        $partnerItems = PartnerItem::where('stock_qty', '>', 0)->where('partner_id', $partnerId)->get();
        $partner = Partner::find($partnerId);
        
        Carbon::setLocale('id');
        $formated_date = Carbon::now()->translatedFormat('d F Y');

        if($type == 'PDF'){
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'margin_right' => 18
            ]);
            
            $pdf->SetTitle('List Konsinyasi ' . $partner->clinic_id . ' - ' . $partner->name . '.pdf');
            $pdf->WriteHTML(view('sell.listKonsinyasiPDF', compact('partnerItems', 'formated_date', 'partner')));
    
            return $pdf->Output('List Konsinyasi ' . $partner->clinic_id . ' - ' . $partner->name . '.pdf', 'I');
        }
        else if($type == 'Excel'){
            return Excel::download(new ListKonsinyasiExport($partnerItems, $formated_date, $partner), 'List Konsinyasi ' . $partner->clinic_id . ' - ' . $partner->name . '.xlsx');
        }
        else{
            return Excel::download(new ListKonsinyasiCSVExport($partnerItems, $partner), 'List Sisa SO ' . $partner->clinic_id . ' - ' . $partner->name . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    public function exportStockRetur(Request $request){
        $partnerId = $request->partnerIdRetur;

        $partnerItems = PartnerItem::withoutGlobalScope('order')
                                    ->select('item_id', DB::raw('sum(stock_qty) as `stock_qty`'))
                                    ->with('item')
                                    ->where('stock_qty', '>', 0)
                                    ->where('partner_id', $partnerId)
                                    ->groupBy('item_id')
                                    ->get()
                                    ->transform(function ($dt) use($partnerId){
                                        $stockOnProcess = DB::table('sell_order_details AS sod')
                                                                        ->join('sell_orders AS so', 'so.id', '=', 'sod.sell_order_id')
                                                                        ->join('partner_item AS pi', 'pi.id', '=', 'sod.item_id')
                                                                        ->where('pi.item_id', $dt->item_id)
                                                                        ->where('sod.quantity', '>', 0)
                                                                        ->where('so.status_id', 1)
                                                                        ->where('so.source_partner_id', $partnerId)
                                                                        ->sum('sod.quantity');

                                        $dt->stock_qty = $dt->stock_qty - $stockOnProcess;
                                        return $dt;
                                    });

        $partner = Partner::find($partnerId);

        $pdf = new Mpdf([
            'margin_top' => 10,
            'margin_bottom' => 20,
            'margin_left' => 18,
            'margin_right' => 18
        ]);
        
        $pdf->SetTitle('List Current Stock ' . $partner->clinic_id . ' - ' . $partner->name . '.pdf');
        $pdf->WriteHTML(view('sell.listStockRetur', compact('partnerItems', 'partner')));

        return $pdf->Output('List Current Stock ' . $partner->clinic_id . ' - ' . $partner->name . '.pdf', 'I');
    }

    public function exportListDetail(Request $request){
        $date = explode(' - ', $request->daterange);
        $startDate = Carbon::createFromFormat('d/m/Y', $date[0])->format('Y-m-d');
        $endDate = Carbon::createFromFormat('d/m/Y', $date[1]);
        $isSellOrder = $request->isSellOrder == "true";
        //add 1 day for sellorder because of time setting = midnight
        if($isSellOrder) $endDate = $endDate->addDay()->format('Y-m-d');
        else $endDate = $endDate->format('Y-m-d');
        
        $type = $request->type;
        $typeName = $isSellOrder ? (in_array($type, ['Reguler', 'Konsinyasi']) ? '-Penjualan' : ''): '-Permintaan';

        $fileName = 'Export-List'. $typeName . '-'.$type;

        if($isSellOrder){
            $datetype = $request->datetype == 'created' ? 'created_at' : 'delivered_at';
            $sellOrder = SellOrder::with(['sourcePartner', 'destinationPartner', 'status', 'partnerItems'])
                                                        ->whereBetween($datetype, [$startDate, $endDate]);
            switch ($type) {
                case 'Reguler':
                    $sellOrder = $sellOrder->where('sell_order_type_id', 1)->get();
                    return Excel::download(new ListSellOrderRegulerExport($sellOrder), $fileName . '.xlsx');
                case 'Konsinyasi':
                    $sellOrder = $sellOrder->where('sell_order_type_id', 2)->get();
                    return Excel::download(new ListSellOrderKonsinyasiExport($sellOrder), $fileName . '.xlsx');
                case 'Transfer':
                    //unused
                    $sellOrder = $sellOrder->where('sell_order_type_id', 3)->get();
                    return Excel::download(new ListSellOrderTransferExport($sellOrder), $fileName . '.xlsx');
                case 'Retur':
                    $sellOrder = $sellOrder->where('sell_order_type_id', 4)->get();
                    return Excel::download(new ListSellOrderReturExport($sellOrder), $fileName . '.xlsx');
                case 'SO':
                    $sellOrder = $sellOrder->where('sell_order_type_id', 5)->get();
                    return Excel::download(new ListSellOrderSOExport($sellOrder), $fileName . '.xlsx');
            }
        }else{
            $datetype = $request->datetype == 'created' ? 'request_date' : 'deliver_date';
            $requestOrder = ConsignmentRequest::with(['partner',  'status', 'items'])
                                                                            ->whereHas('items', function ($query) use ($datetype, $startDate, $endDate) {
                                                                                $query->whereBetween($datetype, [$startDate, $endDate]);
                                                                            });
            switch($type){
                case 'Reguler':
                    $requestOrder = $requestOrder->where('type', $type)->get();
                    return Excel::download(new ListRequestOrderExport($requestOrder), $fileName . '.xlsx');
                case 'Konsinyasi':
                    $requestOrder = $requestOrder->where('type', $type)->get();
                    return Excel::download(new ListRequestOrderExport($requestOrder), $fileName . '.xlsx');
            }
        }
    }

    public function completePermintaan(ConsignmentRequest $consignmentRequest){
        DB::beginTransaction();

        try{
            $consignmentRequest->status_id = 4;
            $consignmentRequest->update();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menyelesaikan permintaan, silahkan coba lagi!';

            return back()->with('error', $error);
        }

        $success = 'Berhasil menyelesaikan permintaan!';

        return redirect()->route('sell.permintaan.index', ['type' => $consignmentRequest->type])->with('success', $success);
    }

    public function exportHasilSO(Request $request){
        $sellId = $request->id;
        $jenisExport = $request->jenisExportSO;
        

        $sellOrder = SellOrder::find($sellId);
        $partner = Partner::find($sellOrder->destination_partner_id);
        
        Carbon::setLocale('id');
        $formated_date = $sellOrder->created_at->translatedFormat('d F Y');

        $partnerItems = $sellOrder->partnerItems;

        $isHasilSO = true;

        if($jenisExport == 'PDF'){
            $pdf = new Mpdf([
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 18,
                'margin_right' => 18,
            ]);
            
            $pdf->SetTitle('List Sisa SO ' . $partner->clinic_id . ' - ' . $partner->name . '.pdf');
            $pdf->WriteHTML(view('sell.listKonsinyasiPDF', compact('partnerItems', 'formated_date', 'partner', 'isHasilSO')));
    
            return $pdf->Output('List Sisa SO ' . $partner->clinic_id . ' - ' . $partner->name . '.pdf', 'I');
        }
        else if($jenisExport == 'Excel'){
            return Excel::download(new ListKonsinyasiExport($partnerItems, $formated_date, $partner, $isHasilSO), 'List Sisa SO ' . $partner->clinic_id . ' - ' . $partner->name . '.xlsx');
        }
        else{
            return Excel::download(new ListSisaSOCSVExport($partnerItems, $partner), 'List Sisa SO ' . $partner->clinic_id . ' - ' . $partner->name . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    public function terimaPesanan(SellOrder $sell, Request $request){
        DB::beginTransaction();

        $sellOrderTypeId = $sell->sellOrderType->id;

        try{
            // Penjualan Reguler
            if($sell->sell_order_type_id == 1){
                foreach($sell->partnerItems as $item){
                    $itemSource = PartnerItem::where('partner_item.id', $item->pivot->item_id)->first();

                    if($itemSource->stock_qty < $item->pivot->quantity){
                        DB::rollBack();
                        $error = 'Gagal menyelesaikan pemesanan, stock item dengan id barcode ' . $itemSource->barcode_id . ' (' . $itemSource->item->name . ') di klinik sumber tidak cukup!';
                        
                        return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
                    }

                    $itemSource->stock_qty = $itemSource->stock_qty - $item->pivot->quantity;
                    $itemSource->update();
                }

                $sell->status_id = 2;

                if($sell->delivered_at == null){
                    $sell->delivered_at = Carbon::now();
                }

                $sell->update();
            }
            // Konsinyasi, dan Retur
            else if(in_array($sell->sell_order_type_id, [2, 4])){
                foreach($sell->partnerItems as $item){
                    $itemSource = PartnerItem::where('partner_item.id', $item->pivot->item_id)->first();

                    if($itemSource->stock_qty < $item->pivot->quantity){
                        DB::rollBack();
                        $error = 'Gagal menyelesaikan pemesanan, stock item dengan id barcode ' . $itemSource->barcode_id . ' (' . $itemSource->item->name . ') di klinik sumber tidak cukup!';
                        
                        if($sellOrderTypeId == 2){
                            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
                        }
                        else{
                            return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
                        }
                    }

                    $itemSource->stock_qty = $itemSource->stock_qty - $item->pivot->quantity;
                    $itemSource->update();

                    $addedItem = PartnerItem::where('barcode_id', $itemSource->barcode_id)
                                            ->where('item_id', $itemSource->item_id)
                                            ->where('partner_id', $sell->destination_partner_id)
                                            ->first();

                    if($addedItem != null){
                        $addedItem->stock_qty = $addedItem->stock_qty + $item->pivot->quantity;
                        $addedItem->update();
                    }
                    else{
                        $addedItem = PartnerItem::create([
                            'item_id' => $itemSource->item_id,
                            'partner_id' => $sell->destination_partner_id,
                            'shelf_id' => null,
                            'barcode_id' => $itemSource->barcode_id,
                            'batch' => $itemSource->batch,
                            'exp_date' => $itemSource->exp_date,
                            'stock_qty' => $item->pivot->quantity,
                            'is_consigned' => 1,
                            'created_at' => Carbon::now()
                        ]);
                    }

                    if($sell->sell_order_type_id == 2){
                        $sell->partnerItems()->attach($addedItem->id, ['quantity' => $item->pivot->quantity, 'total' => $item->pivot->total]);
                    }
                }

                if($sell->sell_order_type_id == 2){
                    $destination = $sell->destination_partner_id;

                    $sell->partnerItems()->each(function ($partnerItem) use ($destination, $sell){
                        if($partnerItem->partner_id != $destination){
                            $sell->partnerItems()->detach($partnerItem->id);
                        }
                    });
                }  

                $sell->status_id = 2;
                if($sell->delivered_at == null){
                    $sell->delivered_at = Carbon::now();
                }

                if($sell->sell_order_type_id != 4 && $sell->status_kode == 'FIRST'){
                    $sell->due_at = Carbon::now()->addWeeks(2);
                }

                $sell->update();
            }
            // Transfer
            else{
                // Document untuk konsinyasi baru setelah transfer
                $latestRegulerKonsinyasi = SellOrder::whereIn('sell_order_type_id', [1, 2])->orderBy('created_at', 'DESC')->first();
                $orderNoKonsinyasi = null;

                if($latestRegulerKonsinyasi != null){
                    $orderNoKonsinyasi = ((int) explode('/', $latestRegulerKonsinyasi->document_number)[0]) + 1;
                }
                else{
                    $orderNoKonsinyasi = 1;
                }

                $documentNumberKonsinyasi = sprintf("%04d", $orderNoKonsinyasi) . '/S18-DOC/SMI/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');

                $checkPartnerAlreadyFirst = SellOrder::where('status_id', 2)->where('sell_order_type_id', 2)->where('status_kode', 'FIRST')->where('destination_partner_id', $sell->destination_partner_id)->orderBy('delivered_at', 'DESC')->count();
                
                if($checkPartnerAlreadyFirst == 0){
                    DB::rollBack();
                    $error = 'Gagal menyelesaikan pemesanan, mitra tujuan masih belum memiliki konsinyasi FIRST!';
                    
                    return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
                }
                else{
                    $latestRO = SellOrder::whereIn('status_id', [1, 2])
                                                ->where('sell_order_type_id', 2)
                                                ->where('destination_partner_id', $sell->destination_partner_id)
                                                ->where('status_kode', 'LIKE', 'RO%')
                                                ->orderBy('delivered_at', 'DESC')
                                                ->first();

                    // Check if FIRST has RO description
                    $first = SellOrder::where('status_id', 2)
                    ->where('sell_order_type_id', 2)
                    ->where('destination_partner_id', $sell->destination_partner_id)
                    ->where('status_kode', 'FIRST')->first();

                    if($latestRO != null){
                        $newNumberRO = ((int) substr($latestRO->status_kode, 2)) + 1;
                        $kodeKonsinyasi = 'RO' . $newNumberRO;
                    }
                    else if(str_contains($first->description, '[RO')){
                        $newNumberRO = explode(']', explode('[RO', $first->description)[1])[0] + 1; 
                        $kodeKonsinyasi = 'RO' . $newNumberRO;
                    }
                    else{
                        $kodeKonsinyasi = 'RO1';
                    }

                    $sellOrder = SellOrder::create([
                        'sell_order_type_id' => 2,
                        'source_partner_id' => 1,
                        'destination_partner_id' => $sell->destination_partner_id,
                        'status_id' => 2,
                        'parent_id' => $sell->id,
                        'document_number' => $documentNumberKonsinyasi,
                        'status_kode' => $kodeKonsinyasi,
                        'total_price' => 0,
                        'description' => $request->description,
                        'delivered_at' => Carbon::now(),
                    ]);
                }
    
                $totalPrice = 0;

                foreach($sell->partnerItems as $item){
                    $itemSource = PartnerItem::where('partner_item.id', $item->pivot->item_id)->first();

                    if($itemSource->stock_qty < $item->pivot->quantity){
                        DB::rollBack();
                        $error = 'Gagal menyelesaikan pemesanan, stock item dengan id barcode ' . $itemSource->barcode_id . ' (' . $itemSource->item->name . ') di klinik sumber tidak cukup!';
                        
                        return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
                    }

                    $itemSource->stock_qty = $itemSource->stock_qty - $item->pivot->quantity;
                    $itemSource->update();

                    $addedItem = PartnerItem::where('barcode_id', $itemSource->barcode_id)
                                            ->where('item_id', $itemSource->item_id)
                                            ->where('partner_id', $sell->destination_partner_id)
                                            ->first();

                    if($addedItem != null){
                        $addedItem->stock_qty = $addedItem->stock_qty + $item->pivot->quantity;
                        $addedItem->update();
                    }
                    else{
                        $addedItem = PartnerItem::create([
                            'item_id' => $itemSource->item_id,
                            'partner_id' => $sell->destination_partner_id,
                            'shelf_id' => null,
                            'barcode_id' => $itemSource->barcode_id,
                            'batch' => $itemSource->batch,
                            'exp_date' => $itemSource->exp_date,
                            'stock_qty' => $item->pivot->quantity,
                            'is_consigned' => 1,
                            'created_at' => Carbon::now()
                        ]);
                    }

                    $sellOrder->partnerItems()->attach($addedItem->id, ['quantity' => $item->pivot->quantity, 'total' => $item->pivot->quantity * (float) $item->discount_price]);
                    $totalPrice += $item->pivot->quantity * (float) $item->discount_price;
                }

                $sellOrder->total_price = $totalPrice;
                $sellOrder->update();

                $sell->status_id = 2;
                $sell->delivered_at = Carbon::now();

                $sell->update();
            }

            if(in_array($sell->sell_order_type_id, [1, 2, 4])){
                $surat_jalan_result = $sell->path_surat_jalan ?? null;

                if($request->file('surat_jalan_result')){
                    if($image = $request->file('surat_jalan_result')){
                        if($sell->sell_order_type_id == 4){
                            $destPath = 'bukti_retur_result/';
                            $fileName = date('YmdHis') . '-Hasil-Bukti-Bayar.' . $image->getClientOriginalExtension();
                            Storage::disk('public')->put($fileName, file_get_contents($image));
                            Storage::disk('public')->move($fileName, 'bukti_retur_result/' . $fileName);
                        }
                        else{
                            $destPath = 'surat_jalan_result/';
                            $fileName = date('YmdHis') . '-Hasil-Surat-Jalan.' . $image->getClientOriginalExtension();
                            Storage::disk('public')->put($fileName, file_get_contents($image));
                            Storage::disk('public')->move($fileName, 'surat_jalan_result/' . $fileName);
                        }

                        $surat_jalan_result = $destPath . $fileName;
                    }
                }
    
                $sell->path_surat_jalan = $surat_jalan_result;
                $sell->update();
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menyelesaikan pemesanan, tolong coba lagi!';

            if($sellOrderTypeId == 1){
                return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
            }
            else if($sellOrderTypeId == 2 || $sellOrderTypeId == 5){
                return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
            }
            else if($sellOrderTypeId == 3){
                return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
            }
            else{
                return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
            }
        }

        $success = 'Berhasil menyelesaikan pemesanan!';

        if($sellOrderTypeId == 1){
            return redirect()->route('sell.index', ['type' => 'Reguler'])->with('success', $success);
        }
        else if($sellOrderTypeId == 2){
            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('success', $success);
        }
        else if($sellOrderTypeId == 3){
            $success = 'Berhasil menyelesaikan pemesanan, konsinyasi baru telah terbuat!';
            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('success', $success);
        }
        else{
            return redirect()->route('sell.index', ['type' => 'Retur'])->with('success', $success);
        }
    }

    public function changeDue($sellId, $date){
        $dateTimeNew = Carbon::createFromFormat('Y-m-d', $date);

        DB::beginTransaction();
        try{
            $sellOrder = SellOrder::find($sellId);

            $sellOrderTypeId = $sellOrder->sell_order_type_id;

            $sellOrder->due_at = $dateTimeNew;
            $sellOrder->update();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengganti due date, tolong coba lagi!';

            if($sellOrderTypeId == 1 || $sellOrderTypeId == 5){
                return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
            }
            else if($sellOrderTypeId == 2){
                return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
            }
            else if($sellOrderTypeId == 3){
                return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
            }
            else{
                return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
            }
        }

        $success = 'Berhasil mengganti due date!';

        if($sellOrderTypeId == 1 || $sellOrderTypeId == 5){
            return redirect()->route('sell.index', ['type' => 'Reguler'])->with('success', $success);
        }
        else if($sellOrderTypeId == 2){
            return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('success', $success);
        }
        else if($sellOrderTypeId == 3){
            return redirect()->route('sell.index', ['type' => 'Transfer'])->with('success', $success);
        }
        else{
            return redirect()->route('sell.index', ['type' => 'Retur'])->with('success', $success);
        }
    }

    public function so(Request $request){
        $sell = SellOrder::where('destination_partner_id', $request->partnerId)->where('status_kode', 'FIRST')->where('status_id', '!=', 3)->first();

        // Check Quantity Konsinyasi Ini
        $partnerItems = PartnerItem::where('partner_id', $sell->destination_partner_id)->where('stock_qty', '>', 0)->get()->transform(function ($dt){
            $stockSynapsa = PartnerItem::where('barcode_id', $dt->barcode_id)->where('partner_id', 1)->sum('stock_qty');

            $dt->stockSynapsa = $stockSynapsa;

            return $dt;
        });

        $synapsa = Partner::find(1);

        $isAlreadyImport = false;

        return view('sell.so', compact('partnerItems', 'synapsa', 'sell', 'isAlreadyImport'));
    }

    public function importSO(Request $request){
        $file = $request->file('file');
        $partnerId = $request->partnerId;

        ModelsSOImport::where('partner_id', $partnerId)->delete();

        Excel::import(new SOImport($partnerId), $file, null, \Maatwebsite\Excel\Excel::CSV);

        $sell = SellOrder::where('destination_partner_id', $partnerId)->where('status_kode', 'FIRST')->where('status_id', '!=', 3)->first();

        // Check Quantity Konsinyasi Ini
        $partnerItems = PartnerItem::where('partner_id', $sell->destination_partner_id)->where('stock_qty', '>', 0)->get()->transform(function ($dt) use ($partnerId){
            $stockAwal = $dt->stock_qty;
            $stockUpdate = ModelsSOImport::where('barcode_id', $dt->barcode_id)->where('partner_id', $partnerId)->first();

            $dt->stockUpdate = $stockUpdate ? $stockUpdate->quantity : 0;
            $dt->stockTerjual = (int) $stockAwal - (int) $dt->stockUpdate;

            return $dt;
        });

        $synapsa = Partner::find(1);

        $isAlreadyImport = true;

        return view('sell.so', compact('partnerItems', 'synapsa', 'sell', 'isAlreadyImport'));
    }

    public function restock(SellOrder $sell){
        // Check Quantity Konsinyasi Ini
        $partnerItems = PartnerItem::where('partner_id', $sell->destination_partner_id)->get()->transform(function ($dt){
            $stockSynapsa = PartnerItem::where('barcode_id', $dt->barcode_id)->where('partner_id', 1)->sum('stock_qty');

            $dt->stockSynapsa = $stockSynapsa;

            return $dt;
        });

        $synapsa = Partner::find(1);
        $idSellOrder = $sell->id;

        return view('sell.restock', compact('partnerItems', 'synapsa', 'idSellOrder'));
    }

    public function storeSoAutofill(Request $request, Partner $partner, SellOrder $sell){
        $isStockOpname = false;
        $isRefill = false;

        if($request->type == 'SO'){
            $isStockOpname = true;
        }

        if($request->type == 'Restock'){
            $isRefill = true;
        }
        
        $sellOrderTypeId = $sell->sell_order_type_id;

        DB::beginTransaction();

        try{
            if($isStockOpname == true){
                $latestStockOpname = SellOrder::where('status_id', 2)->where('sell_order_type_id', 5)->whereMonth('created_at', Carbon::now()->format('m'))->whereYear('created_at', Carbon::now()->year)->orderBy('delivered_at', 'DESC')->first();
                $orderNo = null;
                $status_kode = 1;

                if($latestStockOpname != null){
                    $orderNo = ((int) explode('/', $latestStockOpname->document_number)[0]) + 1;
                }
                else{
                    $orderNo = 1;
                }

                $documentNumber = sprintf("%04d", $orderNo) . '/S18-SOP/MK/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');

                $latestStockOpnameThispartner = SellOrder::where('status_id', 2)->where('sell_order_type_id', 5)->where('destination_partner_id', $partner->id)->orderBy('delivered_at', 'DESC')->first();

                if($latestStockOpnameThispartner != null){
                    $status_kode = $latestStockOpnameThispartner->status_kode + 1;
                }
                else{
                    $status_kode = 1;
                }

                $sellOrder = SellOrder::create([
                    'sell_order_type_id' => 5,
                    'source_partner_id' => 1,
                    'destination_partner_id' => $partner->id,
                    'status_id' => 2,
                    'document_number' => $documentNumber,
                    'total_price' => 0,
                    'description' => $request->description,
                    'delivered_at' => Carbon::now(),
                    'status_kode' => $status_kode
                ]);

                $totalPrice = 0;
                
                foreach($request->barcodeId as $index => $barcode){
                    $itemSource = PartnerItem::where('barcode_id', $barcode)->where('partner_id', $partner->id)->first();

                    if($itemSource->stock_qty < $request->soSoldQuantity[$index]){
                        DB::rollBack();
                        $error = 'Gagal menyelesaikan stock opname, stock item dengan id barcode ' . $itemSource->barcode_id . ' (' . $itemSource->item->name . ') di klinik konsinyasi tidak cukup!';
                        
                        return redirect()->route('sell.index', ['type' => 'SO'])->with('error', $error);
                    }

                    $itemSource->stock_qty = $itemSource->stock_qty - $request->soSoldQuantity[$index];
                    $itemSource->update();

                    $sellOrder->partnerItems()->attach($itemSource->id, ['quantity' => $request->soSoldQuantity[$index], 'quantity_left' => $itemSource->stock_qty, 'total' => $request->soSoldQuantity[$index] * $itemSource->discount_price]);
                    $totalPrice = $totalPrice + $request->soSoldQuantity[$index] * $itemSource->discount_price;
                }

                $sellOrder->total_price = $totalPrice;
                $sellOrder->save();

                $sell->due_at = Carbon::now()->addWeeks(2);
                $sell->save();
            }

            if($isRefill == true){
                $documentNumber = sprintf("%04d", $request->noKonsinyasi) . '/S18-INV/MK/' . $this->convertToGreekNumber(Carbon::now()->format('n')) . Carbon::now()->format('Y');

                $sellOrder2 = SellOrder::create([
                    'sell_order_type_id' => 2,
                    'source_partner_id' => 1,
                    'destination_partner_id' => $sell->destination_partner_id,
                    'status_id' => 1,
                    'document_number' => $documentNumber,
                    'total_price' => 0,
                    'description' => $request->descriptionKonsinyasi
                ]);

                $totalPrice = 0;
                
                foreach($request->barcodeId as $index => $barcode){
                    if($request->refillQuantity[$index] > 0){
                        $itemSource = PartnerItem::where('partner_id', 1)->where('barcode_id', $barcode)->get();
                        
                        $stock = 0;
                        
                        foreach($itemSource as $source){
                            $stock += $source->stock_qty;
                        }
    
                        if($stock < $request->refillQuantity[$index]){
                            DB::rollBack();
                            $error = 'Gagal merefill stock konsinyasi, stock item dengan id barcode ' . $itemSource[0]->barcode_id . ' (' . $itemSource[0]->item->name . ') di klinik pusat tidak cukup!';
                            
                            if($sellOrderTypeId == 1){
                                return redirect()->route('sell.index', ['type' => 'Reguler'])->with('error', $error);
                            }
                            else if($sellOrderTypeId == 2 || $sellOrderTypeId == 5){
                                return redirect()->route('sell.index', ['type' => 'Konsinyasi'])->with('error', $error);
                            }
                            else if($sellOrderTypeId == 3){
                                return redirect()->route('sell.index', ['type' => 'Transfer'])->with('error', $error);
                            }
                            else{
                                return redirect()->route('sell.index', ['type' => 'Retur'])->with('error', $error);
                            }
                        }
    
                        $stockForRefill = $request->refillQuantity[$index];
                        $idx = 0;

                        while($stockForRefill > 0){
                            if($stockForRefill > $itemSource[$idx]->stock_qty){
                                $sellOrder2->partnerItems()->attach($itemSource[$idx]->id, ['quantity' => $itemSource[$idx]->stock_qty, 'total' => $itemSource[$idx]->stock_qty * $itemSource[$idx]->discount_price]);
                                $totalPrice = $totalPrice + ($itemSource[$idx]->stock_qty * $itemSource[$idx]->discount_price);
                                $stockForRefill = $stockForRefill - $itemSource[$idx]->stock_qty;
                                $itemSource[$idx]->stock_qty = 0;
                            }
                            else{
                                $sellOrder2->partnerItems()->attach($itemSource[$idx]->id, ['quantity' => $stockForRefill, 'total' => $stockForRefill * $itemSource[$idx]->discount_price]);
                                $totalPrice = $totalPrice + ($stockForRefill * $itemSource[$idx]->discount_price);
                                $stockForRefill = 0;
                            }

                            $idx++;
                        }
                    }
                }

                $sellOrder2->total_price = $totalPrice;
                $sellOrder2->save();
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            if($isStockOpname == true){
                $error = 'Gagal melakukan SO, tolong coba lagi!';
            }
            else{
                $error = 'Gagal melakukan restock, tolong coba lagi!';
            }

            return redirect()->route('sell.index', ['type' => 'SO'])->with('error', $error);
        }


        if($isStockOpname == true){
            $success = 'Berhasil melakukan SO!';
        }
        else{
            $success = 'Berhasil melakukan Restock!';
        }
        
        return redirect()->route('sell.index', ['type' => 'SO'])->with('success', $success);
    }

    public function uploadButirPembayaran(SellOrder $sell, Request $request){
        $data = $request->all();

        DB::beginTransaction();
        try{
            if(isset($data['file'])){
                if($image = $request->file('file')){
                    $destPath = 'buktiBayar/';
                    $fileName = date('YmdHis') . '-Bukti-Pembayaran.' . $image->getClientOriginalExtension();
                    Storage::disk('public')->put($fileName, file_get_contents($image));
                    Storage::disk('public')->move($fileName, 'buktiBayar/' . $fileName);
                    $data['file'] = $destPath . $fileName;
                }
            }
            else{
                $data['file'] = 'buktiBayar/default.png';
            }

            $sell->buktiPembayaran = $data['file'];
            $sell->save();
            
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengupload bukti bayar, tolong coba lagi!';

            return redirect()->route('sell.show', ['sell' => $sell->id])->with('error', $error);
        }

        $success = 'Berhasil mengupload bukti bayar!';

        return redirect()->route('sell.show', ['sell' => $sell->id])->with('success', $success);
    }
}

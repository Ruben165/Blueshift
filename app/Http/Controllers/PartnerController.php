<?php

namespace App\Http\Controllers;

use App\Exports\AllPartnerExport;
use App\Http\Requests\PartnerRequest;
use App\Models\Group;
use App\Models\LogError;
use App\Models\Partner;
use App\Models\SellOrder;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $dataSupplier = Partner::select(
                                'id',
                                'clinic_id',
                                'name',
                                'email',
                                'phone',
                                'logo',
                                'address',
                                'sales_name',
                                'is_headquarter',
                                'allow_consign')
                                ->with('groups', 'zones')
                                ->get();

            return DataTables::of($dataSupplier)
                ->addColumn('groups', function($p) {
                    return $p->groups->count() > 0 ? $p->groups->pluck('name')->implode(', ') : '-';
                })
                ->addColumn('zones', function ($p) {
                    return $p->zones->count() > 0 ? $p->zones->pluck('name')->implode(', ') : '-';
                })
                ->addColumn('actions', function ($p) {
                    $returnedValue = [];
                    
                    array_push($returnedValue, [
                        "route" => route('mitra.show', ['mitra' => $p->id]),
                        "attr_id" => $p->id,
                        "icon" => 'fas fa-fw fa-cube',
                        "label" => 'Detail',
                        "btnStyle" => 'primary'
                    ]);

                    array_push($returnedValue, [
                        "route" => route('mitra.edit', ['mitra' => $p->id]),
                        "attr_id" => $p->id,
                        "icon" => 'fas fa-fw fa-edit',
                        "label" => 'Edit',
                        "btnStyle" => 'info'
                    ]);
                    
                    if($p->id != 1){
                        array_push($returnedValue, [
                            "route" => route('mitra.destroy', ['mitra' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-trash',
                            "label" => 'Hapus',
                            "btnStyle" => 'danger'
                        ]);
                    } 

                    return $returnedValue;
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'mitra.index';

        return view('partner.index', compact('route', 'success', 'error'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $batchs = Group::all();
        $zones = Zone::all();
        return view('partner.new', compact(['batchs', 'zones']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartnerRequest $request)
    {
        $data = $request->all();

        $allow_consign = array_key_exists('allow_consign', $data);
        $data['is_headquarter'] = 0;
        $data['allow_consign'] = $allow_consign ? 1 : 0;

        DB::beginTransaction();
        try{
            if(isset($data['logo'])){
                if($image = $request->file('logo')){
                    $destPath = 'images/clinicLogo/';
                    $fileName = date('YmdHis') . '-' . $data['name'] . '.' . $image->getClientOriginalExtension();
                    Storage::disk('public')->put($fileName, file_get_contents($image));
                    Storage::disk('public')->move($fileName, 'images/clinicLogo/' . $fileName);
                    $data['logo'] = $destPath . $fileName;
                }
            }
            else{
                $data['logo'] = 'images/clinicLogo/default.png';
            }

            $partner = Partner::create($data);

            if($data['batchId'] != '-'){
                $partner->groups()->attach($data['batchId'], ['created_at' => now()]);
            }

            if($data['zoneId'] != '-'){
                $partner->zones()->attach($data['zoneId'], ['created_at' => now()]);
            }
            
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan mitra baru, tolong coba lagi!';

            return redirect()->route('mitra.index')->with('error', $error);
        }

        $success = 'Berhasil menambahkan mitra '. $request->name .'!';

        return redirect()->route('mitra.index')->with('success', $success);
    }

    /**
     * Display the specified resource.
     */
    public function show(Partner $mitra)
    {
        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $regulars = SellOrder::whereIn('sell_order_type_id', [1, 5])->where('destination_partner_id', $mitra->id)->get()->transform(function ($dt){
            $dt->status_id = $dt->status->name;
            $dt->sell_order_type_id = $dt->sellOrderType->name;
            $dt->created_date = explode(':', date('d-m-Y', strtotime($dt->created_at)))[0];
            $dt->total_price = 'Rp '.number_format($dt->total_price, 2);
            $dt->delivered_at = date('d-m-Y', strtotime($dt->delivered_at) ?? '-');

            return $dt;
        });

        $consignes = SellOrder::where('sell_order_type_id', 2)->where('destination_partner_id', $mitra->id)->get()->transform(function ($dt){
            $dt->status_id = $dt->status->name;
            $dt->created_date = explode(' ', date('d-m-Y', strtotime($dt->created_at)))[0];
            $dt->total_price = 'Rp '.number_format($dt->total_price, 2);
            $dt->delivered_at = date('d-m-Y', strtotime($dt->delivered_at) ?? '-');
            $dt->due_at = date('d-m-Y', strtotime($dt->due_at) ?? '-');

            return $dt;
        });

        return view('partner.detail', compact('mitra', 'success', 'error', 'regulars', 'consignes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partner $mitra)
    {
        $batchs = Group::all();
        $zones = Zone::all();

        return view('partner.edit', compact('mitra', 'batchs', 'zones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PartnerRequest $request, Partner $mitra)
    {
        $data = $request->all();
        
        $allow_consign = array_key_exists('allow_consign', $data);
        $data['allow_consign'] = $allow_consign ? 1 : 0;

        DB::beginTransaction();
        try{
            if(isset($data['logo'])){
                if($image = $request->file('logo')){
                    $destPath = 'images/clinicLogo/';
                    $fileName = date('YmdHis') . '-' . $data['name'] . '.' . $image->getClientOriginalExtension();
                    Storage::disk('public')->put($fileName, file_get_contents($image));
                    Storage::disk('public')->move($fileName, 'images/clinicLogo/' . $fileName);
                    $data['logo'] = $destPath . $fileName;

                    if(Storage::disk('public')->exists($mitra->logo)){
                        Storage::disk('public')->delete($mitra->logo);
                    }
                }
            }

            $mitra->update($data);

            if($data['batchId'] != '-'){
                $mitra->groups()->detach();
                $mitra->groups()->attach($data['batchId'], ['created_at' => now()]);
            }
            else{
                $mitra->groups()->detach();
            }

            if($data['zoneId'] != '-'){
                $mitra->zones()->detach();
                $mitra->zones()->attach($data['zoneId'], ['created_at' => now()]);
            }
            else{
                $mitra->zones()->detach();
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah mitra, tolong coba lagi!';

            return redirect()->route('mitra.index')->with('error', $error);
        }

        $success = 'Berhasil mengubah mitra '. $request->name .'!';

        return redirect()->route('mitra.index')->with('success', $success);
    }

    public function changeLogo(PartnerRequest $request, Partner $mitra){
        $logo = $request->file('logo');

        DB::beginTransaction();
        try{
            if($logo){
                $destPath = 'images/clinicLogo/';
                $fileName = date('YmdHis') . '-' . $mitra->name . '.' . $logo->getClientOriginalExtension();
                Storage::disk('public')->put($fileName, file_get_contents($logo));
                Storage::disk('public')->move($fileName, 'images/clinicLogo/' . $fileName);
                $data['logo'] = $destPath . $fileName;

                if(Storage::disk('public')->exists($mitra->logo)){
                    Storage::disk('public')->delete($mitra->logo);
                }
            }

            $mitra->update($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah logo mitra, tolong coba lagi!';

            return redirect()->route('mitra.show', compact('mitra'))->with('error', $error);
        }

        $success = 'Berhasil mengubah logo mitra '. $mitra->name .'!';

        return redirect()->route('mitra.show', compact('mitra'))->with('success', $success);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $mitra)
    {
        $name = $mitra->name;
        DB::beginTransaction();
        try{
            if(Storage::disk('public')->exists($mitra->logo)){
                Storage::disk('public')->delete($mitra->logo);
            }
            $mitra->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus mitra, tolong coba lagi!';

            return redirect()->route('mitra.index')->with('error', $error);
        }

        $success = 'Berhasil menghapus mitra '. $name .'!';

        return redirect()->route('mitra.index')->with('success', $success);
    }

    public function getMitra(Request $request){
        $search = $request->search ?? null;
        $partners = Partner::query();

        if(isset($request->exclude)){
            $partners->whereNotIn('id', $request->exclude);
        }
        else if(isset($request->include)){
            $partners->whereIn('id', $request->include);
        }

        if(!is_null($search)){
            $partners->where('clinic_id', 'LIKE', '%'.$search.'%')
            ->orWhere('name', 'LIKE', '%'.$search.'%');
        }

        $partners = $partners->limit(5)->get();

        $options = [];

        foreach ($partners as $partner){
            if(isset($request->forDataTable) && $request->forDataTable == 'true'){
                $options[] = [
                    'id' => $partner->name,
                    'text' => $partner->name
                ];
            }
            else{
                $options[] = [
                    'id' => $partner->id,
                    'text' => $partner->name
                ];
            }
        }

        return response()->json(['results' => $options]);
    }

    public function exportAll()
    {
        $partner = Partner::with(['zones', 'groups'])->get();
        $fileName = 'List-All-Mitra';
        return Excel::download(new AllPartnerExport($partner), $fileName . '.xlsx');
    }
}
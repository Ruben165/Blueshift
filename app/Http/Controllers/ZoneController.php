<?php

namespace App\Http\Controllers;

use App\Http\Requests\ZoneRequest;
use App\Models\LogError;
use App\Models\Partner;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $dataGroup = Zone::select(
                                'id',
                                'name')->get();

            return DataTables::of($dataGroup)
                ->addColumn('actions', function ($p) {
                    return [
                        [
                            "route" => route('wilayah.edit', ['wilayah' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit Mitra',
                            "class" => 'Edit',
                            "btnStyle" => 'info'
                        ],
                        [
                            "route" => route('wilayah.destroy', ['wilayah' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-trash',
                            "label" => 'Hapus',
                            "class" => 'Hapus',
                            "btnStyle" => 'danger'
                        ]
                    ];
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'wilayah.index';

        return view('master.zone.index', compact('route', 'success', 'error'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ZoneRequest $request)
    {
        $data = $request->all();

        DB::beginTransaction();
        try{
            Zone::create($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan wilayah mitra baru, tolong coba lagi!';

            return redirect()->route('wilayah.index')->with('error', $error);
        }

        $success = 'Berhasil menambahkan wilayah mitra '. $request->name .'!';

        return redirect()->route('wilayah.index')->with('success', $success);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Zone $wilayah)
    {
        if($request->ajax()){
            $dataGroup = $wilayah->partners;

            return DataTables::of($dataGroup)
                ->addColumn('actions', function ($p) use ($wilayah) {
                    return [
                        [
                            "route" => route('wilayah.partner.destroy', ['wilayah' => $wilayah->id, 'mitra' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-trash',
                            "label" => 'Hapus',
                            "class" => 'Hapus',
                            "btnStyle" => 'danger'
                        ]
                    ];
                })
                ->make();
        }

        $success = session('success') ?? null;
        $error = session('error') ?? null;

        $route = 'wilayah.edit';

        return view('master.zone.edit', compact('wilayah', 'route', 'success', 'error'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ZoneRequest $request, Zone $wilayah)
    {
        $data = $request->all();

        DB::beginTransaction();
        try{
            $wilayah->update($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah nama wilayah mitra, mohon coba kembali!';

            return redirect()->route('wilayah.index')->with('error', $error);
        }

        $success = 'Berhasil mengubah nama wilayah '. $request->name .'!';

        return redirect()->route('wilayah.index')->with('success', $success);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $wilayah)
    {
        $name = $wilayah->name;
        DB::beginTransaction();
        try{
            $wilayah->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus wilayah mitra, tolong coba lagi!';

            return redirect()->route('wilayah.index')->with('error', $error);
        }

        $success = 'Berhasil menghapus wilayah mitra '. $name .'!';

        return redirect()->route('wilayah.index')->with('success', $success);
    }

    public function getPartners($id)
    {
        return response()->json($id);
    }

    public function getAllPartners(Request $request){
        $query = Partner::select('id', 'clinic_id', 'name')
                                    ->whereNotIn('id', function ($q) {
                                        $q->select('partner_id')
                                            ->from('partner_zone');
                                    });
        
        if($request->search){
            $query = $query->where(function($q) use($request){
                $q->where('name', 'like', '%' . $request->search . '%');
                $q->orWhere('clinic_id', 'like', '%' . $request->search . '%');
            });
        }

        $notAttachedPartners = $query->get();

        return response()->json([
            "results" => $notAttachedPartners->transform(function ($partner){
                return[
                    'id' => $partner->id,
                    'text' => $partner->clinic_id . ' - ' . $partner->name
                ]; 
            }),
        ]);
    }

    public function addPartner(Request $request, Zone $wilayah){
        DB::beginTransaction();
        try{
            foreach($request->clinic_id as $mitra_id){
                $wilayah->partners()->attach($mitra_id, ['created_at' => now()]);
            }
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan mitra baru di wilayah ' . $wilayah->name . ', mohon coba lagi!';

            return redirect()->route('wilayah.edit', compact('wilayah'))->with('error', $error);
        }

        $success = 'Berhasil menambahkan mitra baru di wilayah '. $wilayah->name .'!';

        return redirect()->route('wilayah.edit', compact('wilayah'))->with('success', $success);
    }

    public function destroyPartner(Zone $wilayah, Partner $mitra){
        DB::beginTransaction();
        try{
            $wilayah->partners()->detach($mitra->id);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus mitra ' . $mitra->name . ' di wilayah ' . $wilayah->name . ', tolong coba lagi!';

            return redirect()->route('wilayah.edit', compact('wilayah'))->with('error', $error);
        }

        $success = 'Berhasil menghapus mitra ' . $mitra->name . ' di wilayah ' . $wilayah->name . '!';

        return redirect()->route('wilayah.edit', compact('wilayah'))->with('success', $success);
    }
}

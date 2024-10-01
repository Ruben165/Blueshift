<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\LogError;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $dataGroup = Group::select(
                                'id',
                                'name')->get();

            return DataTables::of($dataGroup)
                ->addColumn('actions', function ($p) {
                    return [
                        [
                            "route" => route('batch-mitra.edit', ['batch_mitra' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit Mitra',
                            "class" => 'Edit',
                            "btnStyle" => 'info'
                        ],
                        [
                            "route" => route('batch-mitra.destroy', ['batch_mitra' => $p->id]),
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

        $route = 'batch-mitra.index';

        return view('master.group.index', compact('route', 'success', 'error'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupRequest $request)
    {
        $data = $request->all();

        DB::beginTransaction();
        try{
            Group::create($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan batch mitra baru, tolong coba lagi!';

            return redirect()->route('batch-mitra.index')->with('error', $error);
        }

        $success = 'Berhasil menambahkan batch mitra '. $request->name .'!';

        return redirect()->route('batch-mitra.index')->with('success', $success);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Group $batch_mitra)
    {
        if($request->ajax()){
            $dataGroup = $batch_mitra->partners;

            return DataTables::of($dataGroup)
                ->addColumn('actions', function ($p) use ($batch_mitra) {
                    return [
                        [
                            "route" => route('batch-mitra.partner.destroy', ['batch_mitra' => $batch_mitra->id, 'mitra' => $p->id]),
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

        $route = 'batch-mitra.edit';

        return view('master.group.edit', compact('batch_mitra', 'route', 'success', 'error'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GroupRequest $request, Group $batch_mitra)
    {
        $data = $request->all();

        DB::beginTransaction();
        try{
            $batch_mitra->update($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah nama batch mitra, mohon coba kembali!';

            return redirect()->route('batch-mitra.index')->with('error', $error);
        }

        $success = 'Berhasil mengubah nama batch '. $request->name .'!';

        return redirect()->route('batch-mitra.index')->with('success', $success);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $batch_mitra)
    {
        $name = $batch_mitra->name;
        DB::beginTransaction();
        try{
            $batch_mitra->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus batch mitra, tolong coba lagi!';

            return redirect()->route('batch-mitra.index')->with('error', $error);
        }

        $success = 'Berhasil menghapus batch mitra '. $name .'!';

        return redirect()->route('batch-mitra.index')->with('success', $success);
    }

    public function getPartners($id)
    {
        return response()->json($id);
    }

    public function getAllPartners(Request $request){
        $query = Partner::select('id', 'clinic_id', 'name')
                                    ->whereNotIn('id', function ($q) {
                                        $q->select('partner_id')
                                            ->from('partner_group');
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

    public function addPartner(Request $request, Group $batch_mitra){
        DB::beginTransaction();
        try{
            foreach($request->clinic_id as $mitra_id){
                $batch_mitra->partners()->attach($mitra_id, ['created_at' => now()]);
            }
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan mitra baru di batch ' . $batch_mitra->name . ', mohon coba lagi!';

            return redirect()->route('batch-mitra.edit', compact('batch_mitra'))->with('error', $error);
        }

        $success = 'Berhasil menambahkan mitra baru di batch '. $batch_mitra->name .'!';

        return redirect()->route('batch-mitra.edit', compact('batch_mitra'))->with('success', $success);
    }

    public function destroyPartner(Group $batch_mitra, Partner $mitra){
        DB::beginTransaction();
        try{
            $batch_mitra->partners()->detach($mitra->id);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus mitra ' . $mitra->name . ' di batch ' . $batch_mitra->name . ', tolong coba lagi!';

            return redirect()->route('batch-mitra.edit', compact('batch_mitra'))->with('error', $error);
        }

        $success = 'Berhasil menghapus mitra ' . $mitra->name . ' di batch ' . $batch_mitra->name . '!';

        return redirect()->route('batch-mitra.edit', compact('batch_mitra'))->with('success', $success);
    }
}

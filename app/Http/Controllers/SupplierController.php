<?php

namespace App\Http\Controllers;

use App\Models\LogError;
use App\Models\Supplier;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $dataSupplier = Supplier::select(
                                'id',
                                'supplier_code',
                                'name',
                                'email',
                                'phone',
                                'address',
                                'npwp',
                                'is_active')->get();

            return DataTables::of($dataSupplier)
                ->addColumn('actions', function ($p) {
                    return [
                        [
                            "route" => route('supplier.edit', ['supplier' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit',
                            "btnStyle" => 'info'
                        ],
                        [
                            "route" => route('supplier.destroy', ['supplier' => $p->id]),
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

        $route = 'supplier.index';

        return view('supplier.index', compact('route', 'success', 'error'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('supplier.new');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $data = $request->all();
        $isActive = array_key_exists('isActive', $data);
        $data['is_active'] = $isActive ? 1 : 0;;

        DB::beginTransaction();
        try{
            Supplier::create($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan supplier baru, Mohon coba kembali!';

            return redirect()->route('supplier.index')->with('error', $error);
        }

        $success = 'Berhasil menambahkan supplier '. $request->name .'!';

        return redirect()->route('supplier.index')->with('success', $success);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('supplier.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $data = $request->all();
        $isActive = array_key_exists('isActive', $data);
        $data['is_active'] = $isActive ? 1 : 0;

        DB::beginTransaction();
        try{
            $supplier->update($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah supplier, mohon coba kembali!';

            return redirect()->route('supplier.index')->with('error', $error);
        }

        $success = 'Berhasil mengubah supplier '. $request->name .'!';

        return redirect()->route('supplier.index')->with('success', $success);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $name = $supplier->name;
        DB::beginTransaction();
        try{
            $supplier->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus supplier, Mohon coba kembali!';

            return redirect()->route('supplier.index')->with('error', $error);
        }

        $success = 'Berhasil menghapus supplier '. $name .'!';

        return redirect()->route('supplier.index')->with('success', $success);
    }
}

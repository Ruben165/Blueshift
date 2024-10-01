<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShelfRequest;
use App\Models\Item;
use App\Models\LogError;
use App\Models\Partner;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ShelfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $dataGroup = Shelf::select(
                                'id',
                                'name')->get();

            return DataTables::of($dataGroup)
                ->addColumn('actions', function ($p) {
                    return [
                        [
                            "route" => route('rak.edit', ['rak' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit Rak',
                            "class" => 'Edit',
                            "btnStyle" => 'info'
                        ],
                        [
                            "route" => route('rak.destroy', ['rak' => $p->id]),
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

        $route = 'rak.index';

        return view('master.shelf.index', compact('route', 'success', 'error'));
    }

    public function store(ShelfRequest $request)
    {
        $data = $request->all();

        DB::beginTransaction();
        try{
            Shelf::create($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambahkan rak baru, tolong coba lagi!';

            return redirect()->route('rak.index')->with('error', $error);
        }

        $success = 'Berhasil menambahkan rak '. $request->name .'!';

        return redirect()->route('rak.index')->with('success', $success);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Shelf $rak)
    {
        $success = session('success') ?? null;
        $error = session('error') ?? null;

        if($request->ajax()){
            $data = DB::table('partner_item')
                    ->select('item_id', DB::raw('SUM(stock_qty) as total_qty'))
                    ->where('shelf_id', $rak->id)
                    ->groupBy('item_id')
                    ->get()
                    ->transform(function ($dt){
                        $item = Item::where('id', $dt->item_id)->first();

                        return [
                            'sku' => $item->sku,
                            'item_name' => $item->name .' ('.$item->content.') ('.$item->packaging.').',
                            'qty' => $dt->total_qty
                        ];
                    });

            return DataTables::of($data)->make();
        }

        $route = 'rak.edit';

        return view('master.shelf.edit', compact('rak', 'route', 'success', 'error'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShelfRequest $request, Shelf $rak)
    {
        $data = $request->all();

        DB::beginTransaction();
        try{
            $rak->update($data);
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah nama rak, mohon coba kembali!';

            return redirect()->route('rak.index')->with('error', $error);
        }

        $success = 'Berhasil mengubah nama rak '. $request->name .'!';

        return redirect()->route('rak.index')->with('success', $success);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shelf $rak)
    {
        DB::beginTransaction();
        try{
            DB::table('partner_item')->where('shelf_id', $rak->id)->delete();
            $rak->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus rak, mohon coba kembali!';

            return redirect()->route('rak.index')->with('error', $error);
        }

        $success = 'Berhasil menghapus rak !';

        return redirect()->route('rak.index')->with('success', $success);
    }
}

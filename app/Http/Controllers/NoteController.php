<?php

namespace App\Http\Controllers;

use App\Models\LogError;
use App\Models\Note;
use Illuminate\Http\Request;
use DB;

class NoteController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try{
            Note::create([
                'description' => $request->noteDescription
            ]);

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menambah note, mohon coba kembali!';

            return redirect()->route('dashboard')->with('error', $error);
        }

        $success = 'Berhasil menambahkan note!';

        return redirect()->route('dashboard')->with('success', $success);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try{
            $note = Note::find($request->id);

            $note->description = $request->noteDescription;
            $note->update();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal mengubah note, mohon coba kembali!';

            return redirect()->route('dashboard')->with('error', $error);
        }

        $success = 'Berhasil mengubah note!';

        return redirect()->route('dashboard')->with('success', $success);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();

        try{
            $note = Note::find($request->id);
            $note->delete();

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $error = 'Gagal menghapus note, mohon coba kembali!';

            return redirect()->route('dashboard')->with('error', $error);
        }

        $success = 'Berhasil menghapus note!';

        return redirect()->route('dashboard')->with('success', $success);
    }
}

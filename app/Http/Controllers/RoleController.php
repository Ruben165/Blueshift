<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\User;
use App\Models\LogError;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $roles = Role::select([
                'id',
                'name'
            ])->get();

            return DataTables::of($roles)
                ->addColumn('actions', function ($p) {
                    return [
                        [
                            "route" => route('role.edit', ['role' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit',
                            "btnStyle" => 'info'
                        ],
                        [
                            "route" => route('role.destroy', ['role' => $p->id]),
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

        $route = 'role.index';

        return view('role.index', compact('route', 'success', 'error'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('role.new', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $data = $request->all();

        $result = [
            'type' => 'success',
            'message' => 'Berhasil menambahkan role '. $request->name .'!'
        ];
        DB::beginTransaction();
        try{
            $role = Role::create(['name' => $data['name']]);
            $data['permissions'] = array_map('intval', $data['permissions']);
            foreach($data['permissions'] as $id){
                $permission = Permission::findById($id);
                $role->givePermissionTo($permission);
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type'] = 'error';
            $result['message'] = 'Gagal menambahkan role baru, Mohon coba kembali!';
        }

        return redirect()->route('role.index')->with($result['type'], $result['message']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $hasPermissions = $role->getAllPermissions()
                                ->transform(function($dt){
                                    return $dt->id;
                                })
                                ->toArray();
        return view('role.edit', compact('role', 'permissions', 'hasPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->all();
        $allowedPermissions = $role->getAllPermissions()
                                    ->transform(function($dt){
                                        return $dt->id;
                                    })
                                    ->toArray();
        $newPermissions = array_map('intval', $data['permissions']);

        $result = [
            'type' => 'success',
            'message' => 'Berhasil menambahkan role '. $request->name .'!'
        ];
        DB::beginTransaction();
        try{
            $role->update(['name' => $data['name']]);
            //revoke deleted permissions
            foreach($allowedPermissions as $id){
                if(!in_array($id, $newPermissions)){
                    $permission = Permission::findById($id);
                    $role->revokePermissionTo($permission);
                }
            }
            //add new permissions
            foreach($newPermissions as $id){
                if(!in_array($id, $allowedPermissions)){
                    $permission = Permission::findById($id);
                    $role->givePermissionTo($permission);
                }
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type'] = 'error';
            $result['message'] = 'Gagal menambahkan role baru, Mohon coba kembali!';
        }

        return redirect()->route('role.index')->with($result['type'], $result['message']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $result = [
            'type' => 'success',
            'message' => 'Berhasil menambahkan role '. $role->name .'!'
        ];
        $users = User::role($role->name)->get();
        if(count($users) > 0){
            $result['type'] = 'error';
            $result['message'] = 'Gagal menghapus role, tolong pastikan tidak ada user yang menggunakan role '.$role->name.'!';
            return redirect()->route('role.index')->with($result['type'], $result['message']);
        }
        
        DB::beginTransaction();
        try{
            $role->syncPermissions();
            $role->delete();
            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type'] = 'error';
            $result['message'] = 'Gagal menambahkan role baru, Mohon coba kembali!';
        }
        return redirect()->route('role.index')->with($result['type'], $result['message']);
    }
}

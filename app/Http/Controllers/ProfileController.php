<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\LogError;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use DB;
use Hash;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $roles = User::with('roles')
                            ->get()
                            ->transform(function($dt){
                                $dt->role = $dt->roles[0]->name;
                                return $dt;
                            });

            return DataTables::of($roles)
                ->addColumn('actions', function ($p) {
                    return [
                        [
                            "route" => route('user.edit', ['user' => $p->id]),
                            "attr_id" => $p->id,
                            "icon" => 'fas fa-fw fa-edit',
                            "label" => 'Edit',
                            "btnStyle" => 'info'
                        ],
                        [
                            "route" => route('user.destroy', ['user' => $p->id]),
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

        $route = 'user.index';

        return view('user.index', compact('route', 'success', 'error'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(User $user): View
    {
        $currentRole = $user->getRoleNames();
        $currentRole = $currentRole[0];
        $roles = Role::all();
        
        return view('user.edit', compact('user', 'roles', 'currentRole'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->all();
        $result = [
            'type' => 'success',
            'message' => 'Berhasil mengubah user '. $request->name .'!'
        ];
        DB::beginTransaction();
        try{
            $user->update([
                'name'      => $data['name'],
                'username'  => $data['username'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password'])
            ]);

            $role = Role::findById(intval($data['role']));
            $user->syncRoles([]);
            $user->assignRole($role);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            LogError::insertLogError($e->getMessage());

            $result['type'] = 'error';
            $result['message'] = 'Gagal mengubah user, Mohon coba kembali!';
        }

        return redirect()->route('user.index')->with($result['type'], $result['message']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $loggedIn = Auth::user();
        $name = $user->name;
        $isCurrentUser = false;
        if($user->id === $loggedIn->id){
            Auth::logout();
            $isCurrentUser = true;
        }

        $user->syncRoles([]);
        $user->delete();

        if($isCurrentUser){
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return Redirect::to('/'); 
        }

        return redirect()->route('user.index')->with('success', 'Sukses menghapus user '.$name);
    }
}

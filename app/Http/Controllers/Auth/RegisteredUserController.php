<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogError;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $result = [
            'type' => 'success',
            'message' => 'Berhasil menambahkan role '. $request->name .'!'
        ];
        
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now()
            ]);

            $role = Role::findById(intval($request->role));
            $user->assignRole($role);

            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            LogError::insertLogError('failed to register.'.$th->getMessage() .' | '.$th->getFile().' | '.$th->getLine());

            $result['type'] = 'error';
            $result['message'] = 'Gagal menambahkan user baru. Mohon coba kembali!';
            //throw $th;
        }
        

        return redirect()->route('user.index')->with($result['type'], $result['message']);
    }
}

@extends('layouts.app')

@section('title', 'Edit User')

@section('content_header')
    <div class="content_header">
        <h1>Edit User {{$user->name}}</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary flex flex-col pt-6 sm:pt-0 bg-white dark:bg-white">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <form method="POST" action="{{ route('user.update', ['user' => $user->id]) }}">
                        @csrf
                        @method('PATCH')
            
                        <!-- Name -->
                        <div>
                            <label for="name">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama..." value="{{ old('name') ?? $user->name }}">
                            @if($errors->first('name'))
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                            @endif
                        </div>
            
                        <div class="mt-4">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username..." value="{{ old('username') ?? $user->username }}">
                            @if($errors->first('username'))
                                <p class="text-danger">
                                    {{ $errors->first('username') }}
                                </p>
                            @endif
                        </div>
            
                        <!-- Email Address -->
                        <div class="mt-4">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email..." value="{{ old('email') ?? $user->email }}">
                            @if($errors->first('email'))
                                <p class="text-danger">
                                    {{ $errors->first('email') }}
                                </p>
                            @endif
                        </div>
                        
                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Role')" />
            
                            <x-adminlte-select id="role" name="role">
                                @foreach ($roles as $role) 
                                    <option value="{{$role->id}}" @selected($role->name === $currentRole)>{!! $role->name !!}</option>
                                @endforeach
                            </x-adminlte-select>
            
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>
            
                        <!-- Password -->
                        <div class="mt-4">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password..." value="{{ old('password') }}">
                            @if($errors->first('password'))
                                <p class="text-danger">
                                    {{ $errors->first('password') }}
                                </p>
                            @endif
                        </div>
            
                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Masukkan password kembali..." value="{{ old('password_confirmation') }}">
                            @if($errors->first('password_confirmation'))
                                <p class="text-danger">
                                    {{ $errors->first('password_confirmation') }}
                                </p>
                            @endif
                        </div>
            
                        <div class="flex items-center justify-start mt-4">
                            <x-primary-button class="btn btn-primary">
                                {{ __('Submit') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Tambah Role Baru')

@section('content_header')
    <div class="content_header">
        <h1>Tambah Role Baru</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('role.store') }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Nama Role</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama role..." value="{{ old('name') }}">
                            @if($errors->first('name'))
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <label for="permissions">Permissions</label>
                <div class="col-sm-6">
                    <x-adminlte-select id="permission" name="permissions[]" size="{{count($permissions) > 20 ? 20 : count($permissions)}}" multiple>
                        @foreach ($permissions as $permission) 
                            <option value="{{$permission->id}}">{!! $permission->name !!}</option>
                        @endforeach
                    </x-adminlte-select>
                    @if($errors->first('permission'))
                        <p class="text-danger">
                            {{ $errors->first('permission') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
@endsection

@section('extra-css')
@endsection

@section('extra-js')
@endsection
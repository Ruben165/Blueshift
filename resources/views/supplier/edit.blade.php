@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content_header')
    <div class="content_header">
        <h1>Edit Supplier {{ $supplier->name }}</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('supplier.update', ['supplier' => $supplier->id ]) }}">
            @method('PATCH')
        @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="supplier_code">Kode Supplier</label>
                            <input type="text" class="form-control" id="supplier_code" name="supplier_code" placeholder="Masukkan kode supplier..." value="{{ old('supplier_code') ?? $supplier->supplier_code }}">
                            @if($errors->first('supplier_code'))
                                <p class="text-danger">
                                    {{ $errors->first('supplier_code') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Nama Supplier</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama supplier..." value="{{ old('name') ?? $supplier->name }}">
                            @if($errors->first('name'))
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="npwp">NPWP Supplier</label>
                            <input type="text" class="form-control" id="npwp" name="npwp" placeholder="Masukkan NPWP supplier..." value="{{ old('npwp') ?? $supplier->npwp }}">
                            @if($errors->first('npwp'))
                                <p class="text-danger">
                                    {{ $errors->first('npwp') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="email">Email Supplier</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email supplier..." value="{{ old('email') ?? $supplier->email }}">
                            @if($errors->first('email'))
                                <p class="text-danger">
                                    {{ $errors->first('email') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="phone">No. Telephone Supplier</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan telephone supplier..." value="{{ old('phone') ?? $supplier->phone }}">
                            @if($errors->first('phone'))
                                <p class="text-danger">
                                    {{ $errors->first('phone') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Alamat Supplier</label>
                    <textarea class="form-control" name="address" id="address" cols="30" rows="2" placeholder="Masukkan alamat supplier...">{{ old('address') ?? $supplier->address }}</textarea>
                    @if($errors->first('address'))
                        <p class="text-danger">
                            {{ $errors->first('address') }}
                        </p>
                    @endif
                </div>
                <div class="form-check">
                <input type="checkbox" class="form-check-input" id="isActive" name="isActive" {{ $supplier->is_active == 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Aktifkan Supplier</label>
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
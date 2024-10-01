@extends('layouts.app')

@section('title', 'Edit Item Obat')

@section('content_header')
    <div class="content_header">
        <h1>Edit Item {{ $item->name }}</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('item.update', ['item' => $item->id ]) }}">
        @csrf
        @method('PATCH')
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" placeholder="Masukkan kode SKU..." value="{{ old('sku') ? old('sku') : $item->sku }}">
                            @if($errors->first('sku'))
                                <p class="text-danger">
                                    {{ $errors->first('sku') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Nama Obat</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama obat..." value="{{ old('name') ? old('name') : $item->name }}">
                            @if($errors->first('name'))
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="content">Kandungan Obat</label>
                            <input type="text" class="form-control" id="content" name="content" placeholder="Masukkan kandungan obat..." value="{{ old('content') ? old('content') : $item->content }}">
                            @if($errors->first('content'))
                                <p class="text-danger">
                                    {{ $errors->first('content') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="packaging">Kemasan</label>
                            <input type="text" class="form-control" id="packaging" name="packaging" placeholder="Masukkan packaging obat..." value="{{ old('packaging') ? old('packaging') : $item->packaging }}">
                            @if($errors->first('packaging'))
                                <p class="text-danger">
                                    {{ $errors->first('packaging') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="unit">Satuan</label>
                            <input type="text" class="form-control" id="unit" name="unit" placeholder="Masukkan satuan obat..." value="{{ old('unit') ? old('unit') : $item->unit }}">
                            @if($errors->first('unit'))
                                <p class="text-danger">
                                    {{ $errors->first('unit') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="type_id">Golongan Obat</label>
                            <x-adminlte-select id="type_id" name="type_id">
                                @foreach ($types as $type) 
                                    <option value="{{$type->id}}">{!! $type->name !!}</option>
                                @endforeach
                            </x-adminlte-select>              
                            @if($errors->first('type_id'))
                                <p class="text-danger">
                                    {{ $errors->first('type_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="supplier_id">Supplier</label>
                            <x-adminlte-select id="supplier_id" name="supplier_id">
                                @foreach ($suppliers as $supplier) 
                                    <option value="{{$supplier->id}}">{!! $supplier->name !!}</option>
                                @endforeach
                            </x-adminlte-select>              
                            @if($errors->first('supplier_id'))
                                <p class="text-danger">
                                    {{ $errors->first('supplier_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="manufacturer">Pabrik</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" placeholder="Masukkan pabrik obat..." value="{{ old('manufacturer') ? old('manufacturer') : $item->manufacturer }}">
                            @if($errors->first('manufacturer'))
                                <p class="text-danger">
                                    {{ $errors->first('manufacturer') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="price">Harga</label>
                            <input type="number" step=".01" min="0" class="form-control" id="price" name="price" placeholder="Masukkan harga obat..." value="{{ old('price') ? old('price') : $item->price }}">
                            @if($errors->first('price'))
                                <p class="text-danger">
                                    {{ $errors->first('price') }}
                                </p>
                            @endif
                        </div>
                    </div>
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
@extends('layouts.app')

@section('title', 'Tambah Mitra Baru')

@section('content_header')
    <div class="content_header">
        <h1>Tambah Mitra Baru</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('mitra.store') }}" enctype="multipart/form-data">
        @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="clinic_id">ID Mitra</label>
                            <input type="text" class="form-control" id="clinic_id" name="clinic_id" placeholder="Masukkan ID Mitra..." value="{{ old('clinic_id') }}">
                            @if($errors->first('clinic_id'))
                                <p class="text-danger">
                                    {{ $errors->first('clinic_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Nama Mitra</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama mitra..." value="{{ old('name') }}">
                            @if($errors->first('name'))
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="sales_name">Nama Sales</label>
                            <input type="text" class="form-control" id="sales_name" name="sales_name" placeholder="Masukkan nama sales..." value="{{ old('sales_name') }}">
                            @if($errors->first('sales_name'))
                                <p class="text-danger">
                                    {{ $errors->first('sales_name') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="email">Email Mitra</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email mitra..." value="{{ old('email') }}">
                            @if($errors->first('email'))
                                <p class="text-danger">
                                    {{ $errors->first('email') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="phone">No. Telephone Mitra</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan telephone mitra..." value="{{ old('phone') }}">
                            @if($errors->first('phone'))
                                <p class="text-danger">
                                    {{ $errors->first('phone') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="logo">Logo Mitra (Opsional)</label>
                    <div class="custom-file">
                        <input type='file' class="custom-file-input" name="logo" id="logo" value="{{ old('logo') }}">
                        <label class="custom-file-label" for="logo">Pilih file</label>
                    </div>
                    @if($errors->first('logo'))
                        <p class="text-danger">
                            {{ $errors->first('logo') }}
                        </p>
                    @endif
                </div>
                <div class="form-group">
                    <label for="address">Alamat Mitra</label>
                    <textarea class="form-control" name="address" id="address" cols="30" rows="2" placeholder="Masukkan alamat mitra...">{{ old('address') }}</textarea>
                    @if($errors->first('address'))
                        <p class="text-danger">
                            {{ $errors->first('address') }}
                        </p>
                    @endif
                </div>
                <div class="form-group">
                    <label for="batchId">Pilih Batch (Opsional)</label>
                    <select class="form-control" id="batchId" name="batchId">
                        <option {{ old('batchId') ? '' : 'selected' }} value="-">-Pilih Batch-</option>
                        @foreach ($batchs as $batch)
                        <option value="{{ $batch->id }}" {{ old('batchId') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="zoneId">Pilih Wilayah (Opsional)</label>
                    <select class="form-control" id="zoneId" name="zoneId">
                        <option {{ old('zoneId') ? '' : 'selected' }} value="-">-Pilih Wilayah-</option>
                        @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" {{ old('zoneId') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                <input type="checkbox" class="form-check-input" id="allow_consign" name="allow_consign" checked>
                <label class="form-check-label" for="allow_consign">Konsinyasi</label>
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
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('change', '#logo', function(){
                let filename = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').text(filename)
            })
        })
    </script>
@endsection
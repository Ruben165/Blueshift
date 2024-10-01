@extends('layouts.app')

@section('title', 'Edit Stock')

@section('content_header')
    <div class="content_header">
        <h1>Edit Stock</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('item.stock.update', ['stock' => $data['id'] ]) }}">
        @csrf
        @method('PATCH')
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="itemId">Nama Item</label>
                            <select class="form-control" id="itemId" name="itemId" disabled>
                                <option value="{{ $data['item_id'] }}">{{ $data['item_sku'] . ' - ' . $data['item_name'] }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="partnerId">Nama Mitra</label>
                            <select class="form-control" id="partnerId" name="partnerId" disabled>
                                <option value="{{ $data['partner_id'] }}">{{ $data['partner_clinic_id'] . ' - ' . $data['partner_name'] }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="batchId">Batch</label>
                            <input type="text" name="batchId" id="batchId" class="form-control" value="{{ $data['batch'] }}" disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        @if($data['is_consigned'] == 0)
                        <div class="form-group" id="shelfForm">
                            <label for="shelfId">Rak</label>
                            <select class="form-control" id="shelfId" name="shelfId">
                                @foreach ($shelfs as $shelf)
                                <option value="{{ $shelf->id }}" {{ old('shelfId') == $shelf->id || ($data['rak'] != null && $data['rak'] == $shelf->id) ? 'selected' : '' }}>{{ $shelf->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="exp_date">Tanggal Expired</label>
                            <input type="date" class="form-control" name="exp_date" id="exp_date" value="{{ $data['exp_date'] }}" disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="stock_qty">Kuantitas</label>
                            <input type="number" class="form-control" name="stock_qty" id="stock_qty" placeholder="Masukkan kuantitas stock..." value="{{ old('stock_qty') ?? $data['stock_qty'] }}" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="stock_qty">Harga Diskon</label>
                            <input type="number" class="form-control" name="discount_price" id="discount_price" placeholder="Masukkan harga diskon..." value="{{ old('discount_price') ?? $data['discount_price'] }}">
                            <div class="text-red"><strong>Catatan:</strong> Kosongkan kolom <strong>Harga Diskon</strong> jika produk sedang tidak diskon! (Jangan diisi 0)</div>
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
    <style scoped>
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding: .375rem .75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #555 transparent transparent transparent;
            border-style: solid;
            border-width: 5px 4px 0 4px;
            height: 0;
            left: 50%;
            margin-left: -4px;
            margin-top: -2px;
            position: absolute;
            top: 50%;
            width: 0;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">
        $('#shelfId').select2({
            theme: "bootstrap",
            tags: true
        });
    </script>
@endsection
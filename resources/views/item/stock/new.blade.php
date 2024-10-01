@extends('layouts.app')

@section('title', 'Tambah Stock Baru')

@section('content_header')
    <div class="content_header">
        <h1>Tambah Stock Baru</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('item.stock.store') }}" id="theForm">
        @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="itemId">Pilih Item</label>
                            <select class="form-control" id="itemId" name="itemId">
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="partnerId">Pilih Mitra</label>
                            <select class="form-control" id="partnerId" name="partnerId">
                                @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partnerId') == $partner->id ? 'selected' : '' }}>{{ $partner->clinic_id . ' - ' . $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="batchId">Batch</label>
                            <select class="form-control" id="batchId" name="batchId" required>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group" id="shelfForm">
                            <label for="shelfId">Rak</label>
                            <select class="form-control" id="shelfId" name="shelfId">
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="exp_date">Tanggal Expired</label>
                            <input type="date" class="form-control" name="exp_date" id="exp_date">
                            @if($errors->first('exp_date'))
                                <p class="text-danger">
                                    {{ $errors->first('exp_date') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="stock_qty">Kuantitas</label>
                            <input type="number" class="form-control" name="stock_qty" id="stock_qty" placeholder="Masukkan kuantitas stock..." required>
                            @if($errors->first('stock_qty'))
                                <p class="text-danger">
                                    {{ $errors->first('stock_qty') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="stock_qty">Harga Diskon</label>
                            <input type="number" class="form-control" name="discount_price" id="discount_price" placeholder="Masukkan  harga diskon...">
                            <div class="text-red"><strong>Catatan:</strong> Kosongkan kolom <strong>Harga Diskon</strong> jika produk sedang tidak diskon! (Jangan diisi 0)</div>
                            @if($errors->first('discount_price'))
                                <p class="text-danger">
                                    {{ $errors->first('discount_price') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    <div class="modal hide fade" tabindex="-1" id="modalBarcode">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Export Barcode</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p>Jika ingin mencetak barcode untuk item ini, silahkan tekan tombol 'Export Barcode'!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('item.stock.print-barcode-add') }}">
                @csrf
                    <input type="hidden" name="id_produk" id="id_produk">
                    <input type="hidden" name="qty" id="qty">
                    <input type="hidden" name="exp" id="exp">
                    <input type="hidden" name="no_batch" id="no_batch">
                    <input type="hidden" name="kode_rak" id="kode_rak">
                    <button type="submit" class="btn btn-success" id="exportBarcode">Export Barcode</button>
                </form>
                <button type="button" class="btn btn-primary" id="submitButtonModal">Tambah Stock</button>
            </div>
          </div>
        </div>
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
        let allShelfs = []

        $(document).ready(function() {
            $('#submitButton').on('click', function(e) {
                e.preventDefault();
                
                let id_produk = $('#itemId').val()
                let qty = $('#stock_qty').val()
                let exp = $('#exp_date').val()
                let no_batch = $('#batchId').val()
                let kode_rak = $('#shelfId').val()

                let isNull = false
                let isNullPartner = false

                $.each([id_produk, qty, exp, no_batch, kode_rak], function(index, val){
                    if(val == null || val == ''){
                        if(index == 4){
                            isNull = true
                        }
                        else{
                            isNullPartner = true
                            isNull = true
                        }
                    }
                })

                if($('#partnerId').val() == 1){
                    if(!isNull){
                        $('#id_produk').val(id_produk)
                        $('#qty').val(qty)
                        $('#exp').val(exp)
                        $('#no_batch').val(no_batch)
                        $('#kode_rak').val(kode_rak)

                        $('#modalBarcode').modal({
                            show: true
                        })
                    }
                    else{
                        swal.fire(
                            'Warning!',
                            'Tolong masukkan seluruh data dengan benar!',
                            'error'
                        );
                    }
                }
                else{
                    if(!isNullPartner){
                        $('#theForm').submit()
                    }
                    else{
                        swal.fire(
                            'Warning!',
                            'Tolong masukkan seluruh data dengan benar!',
                            'error'
                        );
                    }
                }
            })

            $('#submitButtonModal').on('click', function(e){
                e.preventDefault()
                $(this).hide()
                $('#exportBarcode').hide()
                $('#theForm').submit()
            })
        })

        $('#partnerId').select2({theme: "bootstrap"})

        $('#batchId').select2({
                    theme: "bootstrap",
                    tags: true,
                    ajax: {
                        url: "{{ route('item.stock.get-item-batch') }}",
                        maximumSelectionLength: 1,
                        data: function (params) {
                            return {
                                search: params.term,
                                partnerId: 1,
                                itemId: $('#itemId').val()
                            }
                        },
                        processResults: function (data) {
                            return data
                        }
                    }
                });

        $('#shelfId').select2({
            theme: "bootstrap",
            tags: true,
            ajax: {
                url: "{{ route('item.stock.get-item-shelf') }}",
                maximumSelectionLength: 1,
                data: function (params) {
                    return {
                        search: params.term,
                    }
                },
                processResults: function (data) {
                    return data
                }
            }
        });
        
        $('#itemId').on('change', function() {
            $('#batchId').val(null).trigger('change')

            let shelfId = allShelfs.find((x) => x.itemId == $('#itemId').val()).shelfName
            let shelfName = allShelfs.find((x) => x.itemId == $('#itemId').val()).shelfName

            // Clearing Options
            $('#shelfId').empty();

            if(shelfName != null){
                let newOption = new Option(shelfName, shelfId, true, false)
                $('#shelfId').append(newOption).trigger('change')
            }
            else{
                $('#shelfId').val(null).trigger('change')
            }
        })

        $('#partnerId').on('change', function() {
            $('#batchId').val(null).trigger('change')
            $('#shelfId').val('-').trigger('change')

            if($(this).val() == 1){
                $('#shelfForm').show();
            }
            else{
                $('#shelfForm').hide();
            }
        })

        $('#itemId').select2({
                    theme: "bootstrap",
                    ajax: {
                        url: "{{ route($route) }}",
                        maximumSelectionLength: 1,
                        data: function (params) {
                            return {
                                search: params.term,
                            }
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(function (item) {
                                    allShelfs.push({
                                        'itemId': item.id,
                                        'shelfId': item.shelfId,
                                        'shelfName': item.shelfName
                                    })

                                    return {
                                        id: item.id,
                                        text: item.text.replace(/[\(\)]/g, " | ")
                                    };
                                })
                            };
                        }
                    },
                });
    </script>
@endsection
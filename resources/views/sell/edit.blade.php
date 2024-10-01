@extends('layouts.app')

@if($type == 'Reguler')
@section('title', 'Edit Penjualan Reguler')
@elseif($type == 'Konsinyasi')
@section('title', 'Edit Konsinyasi')
@elseif($type == 'Transfer')
@section('title', 'Edit Transfer')
@else
@section('title', 'Edit Retur')
@endif

@section('content_header')
    <div class="content_header">
        <h1>Edit {{ $type == 'Reguler' ? 'Penjualan' : '' }} {{ $type }}</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('sell.update', ['sell' => $sell->id]) }}" id='theForm' enctype="multipart/form-data">
        @method('patch')
        @csrf
            <input type="hidden" name="items" id="itemsForm">
            <div class="card-body">
                <div class="row">
                    @if($type == 'Reguler' || $type == 'Konsinyasi')
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="idOrder">Id Pengiriman</label>
                            <input type="text" name="idOrder" id="idOrder" value="{{ $sell->document_number }}" class="form-control" placeholder="Masukkan Id Order...">
                        </div>
                    </div>
                    @endif
                    @if($type != 'Reguler' && $type != 'Konsinyasi')
                    <div class="col-sm-3">
                    @else
                    <div class="col-sm-3" style="display: none;">
                    @endif
                        <div class="form-group">
                            <label for="partnerSourceId">Pilih Mitra Sumber</label>
                            <input type="text" class="form-control" name="partnerSourceId" id="partnerSourceId" partnerId="{{ $sell->source_partner_id }}" value="{{ $sell->sourcePartner->clinic_id . ' - ' . $sell->sourcePartner->name }}" disabled>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="partnerDestinationId">Pilih Mitra Tujuan</label>
                            <input type="text" class="form-control" name="partnerDestinationId" id="partnerDestinationId" partnerId="{{ $sell->source_partner_id }}" value="{{ $sell->destinationPartner->clinic_id . ' - ' . $sell->destinationPartner->name }}" disabled>
                        </div>
                    </div>
                    @if($type == 'Reguler' || $type == 'Konsinyasi')
                    <div class="col-sm-2" id="jenisPenjualanForm">
                        <div class="form-group">
                            <label for="jenisPenjualan">Jenis Penjualan</label>
                            <select class="form-control" id="jenisPenjualan" name="jenisPenjualan">
                                @if($type == 'Reguler')
                                <option value="1">Reguler</option>
                                @elseif($type == 'Konsinyasi')
                                <option value="2">Konsinyasi</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    @endif
                    @if($type == 'Konsinyasi')
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="kodeKonsinyasi">Kode Konsinyasi</label>
                            <input type="text" class="form-control" name="kodeKonsinyasi" id="kodeKonsinyasi" kodeKonsinyasi="{{ $sell->status_kode }}" value="{{ $sell->status_kode == 'AF' ? 'Auto Fill' : ($sell->status_kode == 'RO' ? 'Repeat Order' : 'First') }}" disabled>
                        </div>
                    </div>
                    @endif
                </div>
                @if($type == 'Retur')
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="returDate">Tanggal Retur</label>
                            <input type="datetime-local" name="returDate" id="returDate" class="form-control" value="{{ $sell->returned_at }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="returPIC">PIC Retur</label>
                            <input type="text" name="returPIC" id="returPIC" class="form-control" value="{{ $sell->pic_retur }}">
                        </div>
                    </div>
                </div>
                @endif
                @if($type == 'Reguler' || $type == 'Konsinyasi')
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="created_at">Tanggal Pemesanan</label>
                            <input type="datetime-local" class="form-control" name="created_at" id="created_at" value="{{ $sell->created_at }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="delivered_at">Tanggal Penjualan</label>
                            <input type="datetime-local" class="form-control" name="delivered_at" id="delivered_at" value="{{ $sell->delivered_at ?? null }}">
                        </div>
                    </div>
                </div>
                @endif
                <div class="row">
                    @if($type != 'Reguler' && $type != 'Konsinyasi')
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="itemId">Pilih Item (Masukkan Nama Item)</label>
                            <select class="form-control" id="itemId" name="itemId">
                            </select>
                        </div>
                    </div>
                    @endif
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="barcodeId">Scan Barcode</label>
                            <input type="text" name="barcodeId" id="barcodeId" class="form-control" placeholder="Klik input box ini dan scan barcode...">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="quantity">Kuantitas</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" value=1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <p class="btn btn-primary" id="addItem">Tambah Item</p>
                    </div>
                </div>
                <h4>Cart Item</h4>
                <div class="row">
                    <div class="col-md-12">
                        @if($type == 'Retur')
                            @include('sell.__item-retur-table')
                        @else
                            @include('sell.__item-table')
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="description">Description (Opsional)</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Masukkan description...">{{ $sell->description }}</textarea>
                        </div>
                    </div>
                </div>
                @if($type == 'Retur')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="surat_jalan_result" style="display: block;">Upload Hasil Bukti Retur</label>
                                <div class="row">
                                    @if($sell->path_surat_jalan != null)
                                        <div class="col-md-4">
                                            <a href="{{ asset('storage/' . $sell->path_surat_jalan) }}" target="_blank" class="btn btn-danger">
                                                <div style="display:flex; align-items:center;">
                                                    <span class="fas fa-fw fa-file-pdf"></span><span style="margin-left: 0.25em">Hasil Bukti Retur</span>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        <x-adminlte-input-file name="surat_jalan_result" id="surat_jalan_result" placeholder="Choose a file..." required/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <button id="submitButton" class="btn btn-primary">Edit</button>
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
        let currentItems = {};
        let partnerItems = {};
        $(document).ready(function() {
            $('#addItem').hide()

            // Default date
            Date.prototype.toDateInputValue = (function() {
                let local = new Date(this);
                local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
                return local.toJSON().slice(0,10);
            });

            $('#sellDate').val(new Date().toDateInputValue())

            let counter = 0
            let inputedItems = []

            let sellItems = {!! $sell->partnerItems->toJson() !!}

            inputedItems = sellItems.map((item) => {
                return {
                    id: item.id,
                    item_id: item.item.id,
                    partner_id: item.partner_id,
                    barcode_id: item.barcode_id,
                    nameModified: item.item.name + ' (' + item.item.content + ') (' + item.item.packaging + ').',
                    price: item.item.price,
                    stock_qty: item.stock_qty,
                    quantity: item.pivot.quantity,
                    batchExp: item.batchExp
                }
            })

            counter = inputedItems.length

            if('{{ $type }}' == 'Konsinyasi' && $('#kodeKonsinyasi').attr('kodeKonsinyasi') == 'AF'){
                Swal.fire({
                    title: 'Loading',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                    Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: "{{ route('sell.all-partner-items') }}",
                    method: "GET",
                    data: {
                        destinationPartnerId: $('#partnerDestinationId').attr('partnerId'),
                    }
                }).done(function(data) {
                    partnerItems = data.results
                    Swal.close();
                });
            }

            $('#addItem').click(async function (){
                let itemData

                if($('#barcodeId').val() != null && $('#barcodeId').val() != ''){
                    let allItems = {!! $items->toJson() !!}

                    currentItems = allItems.filter((x) => {
                        return x.partner_id == $('#partnerSourceId').attr('partnerId')
                    })

                    itemData = await fetchItemBarcode(currentItems, $('#barcodeId').val())
                }
                else{
                    itemData = await fetchItem(currentItems, $('#itemId').val())
                }

                let data = {
                    id: itemData.id,
                    item_id: itemData.item_id,
                    partner_id: itemData.partner_id,
                    barcode_id: itemData.barcode_id,
                    nameModified: itemData.item_name,
                    price: itemData.price,
                    stock_qty: itemData.stock_qty,
                    quantity: $('#quantity').val(),
                    batchExp: itemData.batchExp
                }

                if(parseInt(data.quantity) > parseInt(data.stock_qty)){
                    swal.fire(
                        'Warning!',
                        'Kuantitas barang melebih stock yang tersedia dengan id barcode ' + data.barcode_id + '!',
                        'warning'
                    );
                }

                if(inputedItems.find((x) => x.id == data.id)){
                    let sameItem = inputedItems.find((x) => x.id == data.id)

                    if(parseInt(data.quantity) + parseInt(sameItem.quantity) > parseInt(data.stock_qty)){
                        swal.fire(
                            'Warning!',
                            'Kuantitas barang melebih stock yang tersedia dengan id barcode ' + data.barcode_id + '!',
                            'warning'
                        );
                    }

                    sameItem.quantity = parseInt(data.quantity) + parseInt(sameItem.quantity)

                    if('{{ $type }}' == 'Retur'){
                        $('#jumlah-'+itemData.barcode_id).text(formatNumber(parseInt(sameItem.quantity) * parseInt(data.price)))
                    }
                    
                    resetTable(inputedItems);
                }
                else{
                    inputedItems.push(data)
                    counter++

                    if('{{ $type }}' == 'Retur'){
                        $('#jumlah-'+itemData.barcode_id).text(formatNumber(parseInt(data.quantity) * parseInt(data.price)))
                    }

                    let newRow = $('<tr><td>' + counter + '</td><td>' + data.barcode_id + '</td><td>' + data.nameModified + '</td><td>' + data.quantity + '</td><td>' + data.stock_qty + '</td><td>' + data.batchExp + '</td><td>' + formatNumber(parseInt(data.price)) + '</td><td><b>' +  formatNumber(data.price * data.quantity) + '</b></td><td><div class="btn btn-danger btn-sm removeButton"><div style="display:flex; align-items:center;"><span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span></div></div></td></tr>')
                    $('#dataTable tbody').append(newRow)
                }

                $('#itemId').val(null).trigger('change')
                $('#barcodeId').val(null)
                $('#quantity').val(1)
                $(this).hide()

                $('#totalPrice').text(formatNumber(updateTotal(inputedItems, 'price')))
                $('#totalQtyRequest').text(updateTotal(inputedItems, 'qtyRequest'))
                $('#totalQtyReal').text(updateTotal(inputedItems, 'qtyReal'))
            })

            $('#dataTable').on('click', '.removeButton', function() {
                let row = $(this).closest('tr')
                let rowIndex = row.index()
                row.remove()

                inputedItems.splice(rowIndex, 1)

                $('#dataTable tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });

                counter--

                $('#totalPrice').text(formatNumber(updateTotal(inputedItems, 'price')))
                $('#totalQtyRequest').text(updateTotal(inputedItems, 'qtyRequest'))
                $('#totalQtyReal').text(updateTotal(inputedItems, 'qtyReal'))
            })

            $('#submitButton').on('click', function(e) {
                e.preventDefault();
                
                let itemsBought = JSON.stringify(inputedItems)
                let orderNo = $('#orderNo').val()
                let description = $('#description').val()

                if('{{ $type }}' == 'Reguler'){
                    if($('#idOrder').val() == null || $('#idOrder').val() == ''){
                        swal.fire(
                            'Warning!',
                            'Tolong masukkan ID Order pesanan!',
                            'error'
                        )
                        return;
                    }
                }

                if(inputedItems.length <= 0){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan minimal 1 item!',
                        'error'
                    );
                }
                else if(orderNo == "" || orderNo < 0){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan nomor pesanan!',
                        'error'
                    );
                }
                else{
                    $('#itemsForm').val(itemsBought)
                    $(this).hide()

                    $('#theForm').submit()
                }
            })
        })

        function resetTable(inputedItems){
            $('#dataTable tbody').html('');

            inputedItems.map((data, index) => {
                let newRow = $('<tr><td>' + (parseInt(index) + 1) + '</td><td>' + data.barcode_id + '</td><td>' + data.nameModified + '</td><td>' + data.quantity + '</td><td>' + data.stock_qty + '</td><td>' + data.batchExp + '</td><td>' + formatNumber(parseInt(data.price)) + '</td><td><b>' +  formatNumber(data.price * data.quantity) + '</b></td><td><div class="btn btn-danger btn-sm removeButton"><div style="display:flex; align-items:center;"><span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span></div></div></td></tr>')
                $('#dataTable tbody').append(newRow)
            })
        }

        async function fetchItem(currentItems, option) {
            item = currentItems.find((x) => x.id == option);
            return item;
        }

        function countItemBarcode(currentItems, option) {
            item = currentItems.filter((x) => x.barcode_id == option);

            if(item.length > 0){
                return true
            }
            else{
                return false
            }
        }

        async function fetchItemBarcode(currentItems, option) {
            item = currentItems.filter((x) => x.barcode_id == option);

            if(item.length > 1){
                let options = {};
                item.map((x) => {
                    options[x.id] = x.shelfName
                })

                let selectedItem = await new Promise((resolve) => {
                    swal.fire({
                        title: 'Pilih Sumber Rak',
                        input: 'select',
                        inputOptions: options,
                        inputPlaceholder: 'Pilih rak',
                        preConfirm: (value) => {
                            resolve(value);
                        }
                    })
                }) 

                return item.find((x) => x.id == selectedItem)
            }
            else{
                return item[0];
            }
        }

        function formatNumber(number){
            return number.toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR'
            })
        }

        function updateTotal(inputedItems, returned){
            let total = 0

            inputedItems.map((x) => {
                if(returned == 'price'){
                    total += parseInt(x.price) * x.quantity
                }
                else if(returned == 'qtyRequest'){
                    total += parseInt(x.quantity)
                }
                else if(returned == 'qtyReal'){
                    total += parseInt(x.stock_qty)
                }
            })

            return total
        }

        $('#itemId').select2({
                    theme: "bootstrap",
                    ajax: {
                        url: "{{ route('sell.create') }}",
                        maximumSelectionLength: 1,
                        data: function (params) {
                            return {
                                search: params.term,
                                sourcePartnerId: $('#partnerSourceId').attr('partnerId'),
                            }
                        },
                        processResults: function (data) {
                            if(Object.keys(currentItems).length === 0){
                                let allItems = {!! $items->toJson() !!}
                                partnerId = $('#partnerSourceId').attr('partnerId')

                                currentItems = allItems.filter((x) => {
                                    return x.partner_id == partnerId
                                })
                            }

                            return data
                        }
                    },
                });

        
        $('#itemId').on('change', function() {
            if($(this).val() != null && $('#quantity').val() > 0){
                $('#addItem').show()
                $('#barcodeId').val(null)
            }
            else{
                $('#addItem').hide()
            }
        })

        $('#barcodeId').on('input', function() {
            if ($(this).val().length === 12) {
                $(this).blur();

                let barcodeId = $(this).val()
                $('#itemId').val(null).trigger("change")

                if((!Number.isNaN(Number(barcodeId))) == true && $('#quantity').val() > 0){
                    let allItems = {!! $items->toJson() !!}

                    currentItems = allItems.filter((x) => {
                        if('{{ $type }}' == 'Konsinyasi' && $('#kodeKonsinyasi').attr('kodeKonsinyasi') == 'AF'){
                            return partnerItems.includes(x.item_id) && x.partner_id == $('#partnerSourceId').attr('partnerId')
                        }
                        else{
                            return x.partner_id == $('#partnerSourceId').attr('partnerId')
                        }
                    })

                    if(countItemBarcode(currentItems, barcodeId) == true){
                        $('#addItem').show()
                    }
                    else{
                        swal.fire(
                            'Warning!',
                            'Stock dengan id barcode ' + barcodeId + ' tidak ditemukan!',
                            'error'
                        );
                    }
                }
                else{
                    swal.fire(
                        'Warning!',
                        'Stock dengan id barcode ' + barcodeId + ' tidak ditemukan!',
                        'error'
                    );

                    $('#addItem').hide()
                }
            }
            else{
                $('#addItem').hide()
            }
        })

        $('#quantity').on('change', function() {
            if(($('#itemId').val() != null || $('#barcodeId').val() != null) && $(this).val() > 0){
                $('#addItem').show()
            }
            else{
                $('#addItem').hide()
            }
        })
    </script>
@endsection
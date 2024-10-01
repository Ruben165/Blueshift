@extends('layouts.app')

@section('title', 'Edit Permintaan')

@section('content_header')
    @isset($error)
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
            {{ $error }}
        </div>
    @endisset
    @isset($success)
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
            {{ $success }}
        </div>
    @endisset
    <div class="content_header">
        <h1>Edit Permintaan {{ $type }} #{{ $consignmentRequest->id_request }}</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('sell.permintaan.update', ['consignmentRequest' => $consignmentRequest->id]) }}" id='theForm' enctype="multipart/form-data">
        @csrf
            <input type="hidden" name="items" id="itemsForm">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="partnerId">Mitra</label>
                            <select class="form-control" id="partnerId" name="partnerId" disabled>
                                <option value="{{ $consignmentRequest->partner->id }}">{{ $consignmentRequest->partner->clinic_id . ' - ' . $consignmentRequest->partner->name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="requestId">ID Permintaan</label>
                            <input type="text" name="requestId" id="requestId" placeholder="Masukkan ID permintaan..." class="form-control" value="{{ $consignmentRequest->id_request }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="request_date">Tanggal Permintaan</label>
                            <input type="date" name="request_date" id="request_date" class="form-control" value="{{ $consignmentRequestAdditionalData['request_date'] }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="deliver_date">Tanggal Pengiriman</label>
                            <input type="date" name="deliver_date" id="deliver_date" class="form-control" value="{{ $consignmentRequestAdditionalData['deliver_date'] }}">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="sender_id">ID Pengirim</label>
                            <input type="text" name="sender_id" id="sender_id" placeholder="Masukkan ID Pengirim..." class="form-control" value="{{ $consignmentRequestAdditionalData['sender_id'] }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="sender_pic">PIC Pengirim</label>
                            <input type="text" name="sender_pic" id="sender_pic" placeholder="Masukkan PIC Pengirim..." class="form-control" value="{{ $consignmentRequestAdditionalData['sender_pic'] }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="itemId">Pilih Item</label>
                            <select class="form-control" id="itemId" name="itemId">
                            </select>
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
                        <p class="btn btn-success" id="viewStockItem">Lihat Stok</p>
                    </div>
                </div>
                <h4>Cart Item</h4>
                <div class="row">
                    <div class="col-md-12">
                        @include('sell.permintaan.__item-table')
                    </div>
                </div>
                @if($consignmentRequest->path_surat_permohonan_klinik != null)
                <div class="row">
                    <a href="{{ asset('storage/' . $consignmentRequest->path_surat_permohonan_klinik) }}" target="_blank" class="btn btn-info mb-3 ml-2">
                        <span class="fas fa-file-pdf mr-2"></span>Surat Permohonan Klinik
                    </a>
                </div>
                @else
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="path_surat_permohonan_klinik">Surat Permohonan Klinik (Opsional)</label>
                            <input type='file' class="form-control" id="path_surat_permohonan_klinik" name="path_surat_permohonan_klinik">
                        </div>
                    </div>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="description">Description (Opsional)</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Masukkan description...">{{ $consignmentRequest->description }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button id="submitButton" class="btn btn-primary">Edit</button>
            </div>
        </form>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalListStock">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 90%">
          <div class="modal-content px-2">
            <div class="modal-header">
              <h5 class="modal-title">List Stock Available</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div style="max-width: 100%" class="px-2">
                @include('item.stock.__table')
            </div>
          </div>
        </div>
    </div>
@endsection

@section('extra-css')
    <style scoped>
        .content_header{
            display: flex;
            justify-content: space-between;
        }
        .right_header{
            display: flex;
        }
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da !important;
            background-color: white;
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
        function initSupplierDatatable(query = ""){
            $("#stock-list thead th:nth-child(17), #stock-list tbody")?.remove()
            let table = $('#stock-list').DataTable({
                "destroy": true,
                "processing": true,
                "serverSide": true,
                searching: false,
                "ajax": "{{ route($routeSearch, []) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "barcode_id",
                        "name": "barcode_id",
                        "targets": 1
                    },
                    {
                        "data": "sku",
                        "name": "sku",
                        "targets": 2
                    },
                    {
                        "data": "item_name",
                        "name": "item_name",
                        "targets": 3
                    },
                    {
                        "data": "type_id",
                        "name": "type_id",
                        "targets": 4
                    },
                    {
                        "data": "unit",
                        "name": "unit",
                        "targets": 5
                    },
                    {
                        "data": "pabrik",
                        "name": "pabrik",
                        "targets": 6
                    },
                    {
                        "data": "supplier_id",
                        "name": "supplier_id",
                        "targets": 7
                    },
                    {
                        "data": "price",
                        "name": "price",
                        "targets": 8
                    },
                    {
                        "data": "discounted",
                        "name": "discounted",
                        "targets": 9
                    },
                    {
                        "data": "partner_name",
                        "name": "partner_name",
                        "targets": 10
                    },
                    {
                        "data": "stock_qty",
                        "name": "stock_qty",
                        "targets": 11
                    },
                    {
                        "data": "stock_process",
                        "name": "stock_process",
                        "targets": 12
                    },
                    {
                        "data": "exp_date",
                        "name": "exp_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.exp_date.display
                        },
                        "targets": 13,
                    },
                    {
                        "data": "batch",
                        "name": "batch",
                        "targets": 14
                    },
                    {
                        "data": "shelf_id",
                        "name": "shelf_id",
                        "targets": 15
                    },
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": [0]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false,
                    return: true
                },
                "searchCols": [
                    null,null,
                    { "search" : "^"+query+"$", "regex": true },
                    null, null, null, null, null, null, null, null, null, null, null, null, null, null
                ],
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                },
            })
        }

        let currentItems = {}
        let totalQtyRequest = 0;
        let totalQtySend = 0;
        let isChanged = false;

        $(document).ready(function() {
            $('#addItem').hide()
            $('#viewStockItem').hide()

            // Default date
            Date.prototype.toDateInputValue = (function() {
                let local = new Date(this);
                local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
                return local.toJSON().slice(0,10);
            });

            let inputedItems = {!! $inputedItemsPre->toJson() !!}
            let counter = inputedItems.length

            addNewRow()

            $(document).on('click', '#viewStockItem', function() {
                let itemData = fetchItem(currentItems, $('#itemId').val())
                if(isChanged){
                    initSupplierDatatable(itemData.sku)
                    isChanged = false
                }

                $('#modalListStock').modal({
                    show: true
                })
            })

            $('#addItem').click(function (){
                let itemData = fetchItem(currentItems, $('#itemId').val())

                let data = {
                    itemId: itemData.id,
                    sku: itemData.sku,
                    nameModified: itemData.name + ' (' + itemData.content + ') (' + itemData.packaging + ') (' + itemData.manufacturer +').',
                    quantity: $('#quantity').val(),
                    qty_send: 0
                }

                addNewRow(data)

                $('#itemId').val(null).trigger('change')
                $('#quantity').val(1)
                $(this).hide()
            })

            $('#dataTable').on('click', '.addButton', function() {
                let sku = $(this).attr('sku')

                item = inputedItems.find(x => x.sku == sku)

                if(item){
                    let newData = {
                        itemId: item.itemId,
                        sku: item.sku,
                        nameModified: item.nameModified,
                        quantity: 1,
                        qty_send: 0
                    }

                    addNewRow(newData)
                }
            })

            $('#dataTable').on('click', '.viewStockItem', function() {
                let sku = $(this).attr('sku')
                initSupplierDatatable(sku)

                $('#modalListStock').modal({
                    show: true
                })
            })

            $('#dataTable').on('click', '.removeButton', function() {
                let row = $(this).closest('tr')
                let rowIndex = row.index()

                inputedItems.splice(rowIndex, 1)

                updateTable()
            })

            $('#submitButton').on('click', function(e) {
                e.preventDefault();
                let partnerId = $('#partnerId').val()

                $('#dataTable tbody tr').each(function(idx) {
                    inputedItems[idx].quantity = $(this).find('.qty_request').val();
                    inputedItems[idx].qty_send = $(this).find('.qty_send').val();
                })
                
                let itemsRequested = JSON.stringify(inputedItems)

                if(inputedItems.length <= 0){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan minimal 1 item!',
                        'error'
                    );
                }
                else if($('#partnerId').val() == null){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan nama mitra!',
                        'error'
                    );
                }
                else if($('#requestId').val() == null || $('#requestId').val() == ''){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan ID permintaan!',
                        'error'
                    );
                }
                else{
                    $('#itemsForm').val(itemsRequested)

                    $(this).hide()

                    $('#theForm').submit()
                }
            })

            function addNewRow(data){
                inputedItems = regenerateItems(inputedItems, data)

                updateTable()
            }

            function regenerateItems(allItems, newItem = null){
                if(newItem){
                    allItems.push(newItem)

                    allItems.sort(function(a, b){
                        return (a.nameModified > b.nameModified) ? 1 : ((b.nameModified > a.nameModified) ? -1 : 0)
                    })
                }

                return allItems
            }

            $(document.body).on('change', '.qty_request', function(){
                let val = $(this).val()
                let idx = $(this).attr('idx')

                let item = inputedItems[idx]
                item.quantity = val

                updateTable()
            })

            $(document.body).on('change', '.qty_send', function(){
                let val = $(this).val()
                let idx = $(this).attr('idx')

                let item = inputedItems[idx]
                item.qty_send = val
                updateTable()
            })

            function updateTable(){
                $('#dataTable tbody').html('')
                counter = 1

                totalQtyRequest = 0
                totalQtySend = 0

                inputedItems.map((item, idx) => {
                    let newRow = `
                        <tr>
                            <td>${counter++}</td>
                            <td>${item.nameModified}</td>
                            <td>${item.sku}</td>
                            <td>
                                <input type='number' min=0 class='form-control qty_request' name='qty_request' idx=${idx} value=${item.quantity}>    
                            </td>
                            <td>
                                <input type='number' min=0 class='form-control qty_send' name='qty_send' idx=${idx} value=${item.qty_send}>
                            </td>
                            <td style="display:flex; column-gap: .5rem;">
                                <div class="btn btn-primary btn-sm addButton" sku='${item.sku}'>
                                    <div style="display:flex; align-items:center;">
                                        <span class="fas fa-fw fa-plus"></span><span style="margin-left: 0.25em">Tambah</span>
                                    </div>
                                </div>
                                <div class="btn btn-danger btn-sm removeButton">
                                    <div style="display:flex; align-items:center;">
                                        <span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span>
                                    </div>
                                </div>
                                <div class="btn btn-success btn-sm viewStockItem" sku='${item.sku}'>
                                    <div style="display:flex; align-items:center;">
                                        <span class="fas fa-fw fa-search"></span><span style="margin-left: 0.25em">Lihat Stok</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `
    
                    $('#dataTable tbody').append(newRow)

                    totalQtyRequest += parseInt(item.quantity)
                    totalQtySend += parseInt(item.qty_send)
                })

                updateTotal()
            }
            
            function updateTotal(){
                $('#totalReq').text(totalQtyRequest)
                $('#totalSend').text(totalQtySend)
            }
        })

        function fetchItem(currentItems, option) {
            item = currentItems.find((x) => x.id == option);
            return item;
        }

        function formatNumber(number){
            return number.toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR'
            })
        }

        $('#partnerId').select2({theme: "bootstrap"})

        $('#itemId').select2({
                    theme: "bootstrap",
                    ajax: {
                        url: "{{ route('sell.permintaan.create', ['type' => $type]) }}",
                        maximumSelectionLength: 1,
                        data: function (params) {
                            return {
                                search: params.term,
                            }
                        },
                        processResults: function (data) {
                            currentItems = data.results.map((x) => {
                                return x.itemValues
                            })
                            
                            return data
                        }
                    }
                });

        $('#itemId').on('change', function() {
            if($(this).val() != null && $('#quantity').val() > 0){
                isChanged = true
                $('#addItem').show()
                $('#viewStockItem').show()
            }
            else{
                $('#addItem').hide()
                $('#viewStockItem').hide()
            }
        })

        $('#quantity').on('change', function() {
            if($('#itemId').val() != null && $(this).val() > 0){
                $('#addItem').show()
                $('#viewStockItem').show()
            }
            else{
                $('#addItem').hide()
                $('#viewStockItem').hide()
            }
        })
    </script>
@endsection
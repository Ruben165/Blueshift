@extends('layouts.app')

@section('title', 'Edit Pembelian')

@section('content_header')
    <div class="content_header">
        <h1>Edit Pembelian {{ $buy->SP_no }}</h1>
    </div>
@endsection

@section('content')
    <div class="card card-primary">
        <form method="POST" action="{{ route('buy.update', ['buy' => $buy->id]) }}" id='theForm' enctype="multipart/form-data">
        @csrf
        @method('patch')
            <input type="hidden" name="items" id="itemsForm">
            <input type="hidden" name="faktur" id="fakturForm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="SPNo">No SP:</label>
                            <input type="text" name="SPNo" id="SPNo" class="form-control" placeholder="Masukkan nomor SP..." value="{{ $buy->SP_no }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="SPDate">Tanggal SP:</label>
                            <input type="datetime-local" name="SPDate" id="SPDate" class="form-control" value="{{ $buy->SP_date }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="approveDate">Tanggal Approve:</label>
                            <input type="datetime-local" name="approveDate" id="approveDate" class="form-control" value="{{ $buy->approve_date }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sendDate">Tanggal Kirim:</label>
                            <input type="datetime-local" name="sendDate" id="sendDate" class="form-control" value="{{ $buy->send_date }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="receiveDate">Tanggal Terima:</label>
                            <input type="datetime-local" name="receiveDate" id="receiveDate" class="form-control" value="{{ $buy->receive_date }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="supplierId" style="display: block;">PBF:</label>
                            <select class="form-control" id="supplierId" name="supplierId">
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->supplier_code . ' - ' . $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">Jenis Obat:</label>
                            <select class="form-control" id="type" name="type">
                                @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->document_code . ' - ' . $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <h4 class="mt-3">List Faktur</h4>
                <div class="row">
                    <div class="col-md-6">
                        @include('buy.__faktur-table')
                    </div>
                </div>
                <h4>Cart Item</h4>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="listPembelian" class="btn btn-success mb-3">
                                <span class="fas fa-plus mr-2"></span>Import List Pembelian
                            </label>
                            <input type="file" name="listPembelian" id="listPembelian" hidden>
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
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('buy.__item-table')
                    </div>
                </div>
                {{-- <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="path">Upload Surat Approval</label>
                            <input type="file" name="path" id="path" class="form-control">
                        </div>
                    </div>
                </div> --}}
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
        let listFaktur = {!! json_encode($buy->faktur) !!};
        let inputedItems = {!! json_encode($listItems) !!};
        let counter = 0

        $(document).ready(function() {
            // Preventing Enter for Submiting Form
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                event.preventDefault();
                return false;
                }
            });

            $('#addItem').hide()

            // Default date
            // Date.prototype.toDateInputValue = (function() {
            //     let local = new Date(this);
            //     local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
            //     return local.toJSON().slice(0,10);
            // });

            // $('#buyDate').val(new Date().toDateInputValue())

            $('#addItem').click(function (){
                let itemData = fetchItem(currentItems, $('#itemId').val())
                let lastInputedItems = inputedItems[inputedItems.length-1] || null

                let data = {
                    id: lastInputedItems ? lastInputedItems.id+1 : 0,
                    itemId: itemData.id,
                    name: itemData.name,
                    quantityRequest: $('#quantity').val(),
                    idCRPOBR: null,
                    clinic: null,
                    quantityCame: 0,
                    fakturItem: '',
                    batch: null,
                    expired: '',
                    shelf: null,
                    HNAEach: 0,
                    discount: 0,
                    buyPrice: 0,
                    amount: 0,
                    note: null,
                }

                inputedItems.push(data)
                
                let newRow = `
                                <tr>
                                    <td class="no">
                                        ${++counter}
                                    </td>
                                    <td>
                                        ${data.name}
                                    </td>
                                    <td>
                                        <input type="number" id="quantityRequest-${data.id}" class="form-control quantityRequest" value="${data.quantityRequest}">
                                    </td>
                                    <td>
                                        <input type="text" id="idCRPOBR-${data.id}" class="form-control idCRPOBR">
                                    </td>
                                    <td>
                                        <input type="text" id="clinic-${data.id}" class="form-control clinic">
                                    </td>
                                    <td>
                                        <input type="number" id="quantityCame-${data.id}" class="form-control quantityCame">
                                    </td>
                                    <td>
                                        <select id="faktur-${data.id}" class="form-control fakturItem">
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" id="batch-${data.id}" class="form-control batch">
                                    </td>
                                    <td>
                                        <input type="month" id="expired-${data.id}" class="form-control expired">
                                    </td>
                                    <td>
                                        <select id="shelf-${data.id}" class="form-control shelf">
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" id="HNAEach-${data.id}" class="form-control HNAEach">
                                    </td>
                                    <td>
                                        <input type="number" id="discount-${data.id}" class="form-control discount" value="0">
                                    </td>
                                    <td id="buyPrice-${data.id}">
                                        ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100))}
                                    </td>
                                    <th id="jumlah-${data.id}">
                                        ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100) * parseInt(data.quantityCame))}
                                    </th>
                                    <td>
                                        <input type="text" id="note-${data.id}" class="form-control note">
                                    </td>
                                    <td>
                                        <div class="btn btn-danger btn-sm removeButton">
                                            <div style="display:flex; align-items:center;">
                                                <span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `

                $('#dataTable tbody').append(newRow)

                addSelect2Faktur()
                addSelect2Batch(data.id, data.itemId)
                addSelect2Shelf(data.id, data.itemId)

                if(inputedItems.length > 0){
                    $('#supplierId').prop('disabled', 'disabled')
                    $('#type').prop('disabled', 'disabled')
                }
                else{
                    $('#supplierId').prop('disabled', false)
                    $('#type').prop('disabled', false)
                }

                $('#itemId').val(null).trigger('change')
                $('#quantity').val(1)
                $(this).hide()

                recountTotal()
            })

            $('#dataTable').on('click', '.removeButton', function() {
                let row = $(this).closest('tr')
                let rowIndex = row.index()
                row.remove()

                inputedItems.splice(rowIndex, 1)

                if(inputedItems.length > 0){
                    $('#supplierId').prop('disabled', 'disabled')
                    $('#type').prop('disabled', 'disabled')
                }
                else{
                    $('#supplierId').prop('disabled', false)
                    $('#type').prop('disabled', false)
                }

                $('#dataTable tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });

                counter--

                recountTotal()
            })

            $('#submitButton').on('click', function(e) {
                e.preventDefault();
                
                let itemsBought = JSON.stringify(inputedItems)
                let faktur = JSON.stringify(listFaktur)
                let SPNo = $('#SPNo').val()
                let SPDate = $('#SPDate').val()
                let approveDate = $('#approveDate').val()
                let sendDate = $('#sendDate').val()
                let receiveDate = $('#receiveDate').val()
                let supplierId = $('#supplierId').val()
                let type = $('#type').val()
                // let suratApproval = $('#path')[0].files
                // let isProfileNull = false

                // if(namaPetugas == '' || jabatanPetugas == '' || sipaPetugas == ''){
                //     isProfileNull = true;
                // }

                if(inputedItems.length <= 0){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan minimal 1 item!',
                        'error'
                        );
                }
                // else if(suratApproval.length == 0){
                //     swal.fire(
                //         'Warning!',
                //         'Tolong upload surat approval!',
                //         'error'
                //     )
                // }
                // else if(isProfileNull == true){
                //     swal.fire(
                //         'Warning!',
                //         'Tolong masukkan identitas petugas!',
                //         'error'
                //     );
                // }
                // else if(buyDate == null || buyDate == ''){
                //     swal.fire(
                //         'Warning!',
                //         'Tolong masukkan tanggal pesanan!',
                //         'error'
                //     );
                // }
                else if(SPNo == null || SPNo == ''){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan nomor SP!',
                        'error'
                    )
                }
                else{
                    $('#itemsForm').val(itemsBought)
                    $('#fakturForm').val(faktur)
                    
                    $('#supplierId').prop('disabled', false)
                    $('#type').prop('disabled', false)

                    $(this).hide()

                    $('#theForm').submit()
                }
            })

            $('#listPembelian').on('change', function(){
                let file = $(this)[0].files

                if(file.length > 0){
                    let form = new FormData()

                    form.append('listPembelian', file[0])
                    form.append('type', $('#type').val())
                    form.append('supplierId', $('#supplierId').val())
                    form.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute("content"))

                    Swal.fire({
                        title: 'Mengupload',
                        text: 'Mohon tunggu...',
                        allowOutsideClick: false,
                        onBeforeOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('buy.upload-items') }}",
                        method: 'post',
                        data: form,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response){
                            if(response.some(x => x.hasOwnProperty('notFound'))){
                                swal.close();
                                swal.fire(
                                    'Gagal!',
                                    'Terjadi kesalahan, terdapat item yang tidak ditemukan. <br><br> Hal ini bisa disebabkan karena ID SKU tidak tercatat, salah jenis obat, atau item berasal dari PBF yang tidak tepat.',
                                    'error'
                                );
                            }
                            else{
                                response.map((item) => {
                                    let lastInputedItems = inputedItems[inputedItems.length-1] || null
    
                                    let data = {
                                        id: lastInputedItems ? lastInputedItems.id+1 : 0,
                                        itemId: item.itemId,
                                        name: item.name,
                                        quantityRequest: item.quantityRequest,
                                        idCRPOBR: item.idCRPOBR,
                                        clinic: item.clinic,
                                        quantityCame: item.quantityCame,
                                        fakturItem: item.fakturItem,
                                        batch: item.batch,
                                        expired: item.expired,
                                        shelf: item.shelf,
                                        HNAEach: item.HNAEach,
                                        discount: item.discount,
                                        buyPrice: item.buyPrice,
                                        amount: item.amount,
                                        note: item.note,
                                    }

                                    if(data.fakturItem){
                                        data.fakturItem = checkInsertFaktur(data.fakturItem)
                                    }
    
                                    inputedItems.push(data)
                                    
                                    let newRow = `
                                                    <tr>
                                                        <td class="no">
                                                            ${++counter}
                                                        </td>
                                                        <td>
                                                            ${data.name}
                                                        </td>
                                                        <td>
                                                            <input type="number" id="quantityRequest-${data.id}" class="form-control quantityRequest" value="${data.quantityRequest}">
                                                        </td>
                                                        <td>
                                                            <input type="text" id="idCRPOBR-${data.id}" class="form-control idCRPOBR" value="${data.idCRPOBR || ''}">
                                                        </td>
                                                        <td>
                                                            <input type="text" id="clinic-${data.id}" class="form-control clinic" value="${data.clinic || ''}">
                                                        </td>
                                                        <td>
                                                            <input type="number" id="quantityCame-${data.id}" class="form-control quantityCame" value="${data.quantityCame || 0}">
                                                        </td>
                                                        <td>
                                                            <select id="faktur-${data.id}" class="form-control fakturItem">
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" id="batch-${data.id}" class="form-control batch">
                                                        </td>
                                                        <td>
                                                            <input type="month" id="expired-${data.id}" class="form-control expired" value="${data.expired || null}">
                                                        </td>
                                                        <td>
                                                            <input type="text" id="shelf-${data.id}" class="form-control shelf">
                                                        </td>
                                                        <td>
                                                            <input type="number" id="HNAEach-${data.id}" class="form-control HNAEach" value="${data.HNAEach || 0}">
                                                        </td>
                                                        <td>
                                                            <input type="number" id="discount-${data.id}" class="form-control discount" value="${data.discount || 0}">
                                                        </td>
                                                        <td id="buyPrice-${data.id}">
                                                            ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100))}
                                                        </td>
                                                        <th id="jumlah-${data.id}">
                                                            ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100) * parseInt(data.quantityCame))}
                                                        </th>
                                                        <td>
                                                            <input type="text" id="note-${data.id}" class="form-control note" value="${data.note || null}">
                                                        </td>
                                                        <td>
                                                            <div class="btn btn-danger btn-sm removeButton">
                                                                <div style="display:flex; align-items:center;">
                                                                    <span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                `
    
                                    $('#dataTable tbody').append(newRow)
    
                                    addSelect2Faktur()
                                    addSelect2Batch(data.id, data.itemId, data.batch)
                                    addSelect2Shelf(data.id, data.shelf, data.shelf)
                                })
    
                                if(inputedItems.length > 0){
                                    $('#supplierId').prop('disabled', 'disabled')
                                    $('#type').prop('disabled', 'disabled')
                                }
                                else{
                                    $('#supplierId').prop('disabled', false)
                                    $('#type').prop('disabled', false)
                                }
    
                                recountTotal()
                                swal.close();
                            }

                        },
                        error: function(response){
                            swal.close();
                            swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan, mohon masukkan file dengan data yang benar!',
                                'error'
                            );
                        }
                    })

                    $(this).val('')
                }
            })
            
            // Open Modal Faktur
            $(document).on('click', '#addFaktur', function() {
                $('#noFaktur').val(null).trigger('change')
                $('#dateFaktur').val(null).trigger('change')

                $('#modalAddFaktur').modal({
                    show: true
                })
            })

            // Add Faktur @ Modal
            $(document).on('click', '#addFakturButton', function() {
                if(![null, ''].includes($('#noFaktur').val()) && ![null, ''].includes($('#dateFaktur').val())){
                    let newId = listFaktur.length == 0 ? 1 : listFaktur[listFaktur.length - 1].id + 1

                    listFaktur.push({
                        'id': newId,
                        'faktur': $('#noFaktur').val() + ' | ' + $('#dateFaktur').val()
                    })

                    $('#modalAddFaktur').modal('hide')

                    resetTableFaktur(listFaktur)
                }
            })

            // Remove Faktur
            $('#fakturTable').on('click', '.removeFakturButton', function() {
                let alert = false;

                listFaktur = listFaktur.filter((x) => {
                    if(x.id != $(this).attr('id').split('-')[1]){
                        return true;
                    }
                    else if(inputedItems.find(y => y.fakturItem == x.id)){
                        alert = true;
                        return true;
                    }
                })

                if(alert){
                    swal.fire(
                        'Gagal!',
                        'Faktur sedang digunakan dalam list Pembelian, pastikan tidak ada yang menggunakan faktur ini sebelum menghapus!',
                        'error'
                    );
                }else{
                    resetTableFaktur(listFaktur)
                }
            })

            // On Change Quantity Request
            $(document).on('change', '.quantityRequest', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).quantityRequest = $(this).val()
                recountTotal()
            })

            // On Change ID CR/PO/BR
            $(document).on('change', '.idCRPOBR', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).idCRPOBR = $(this).val()
            })

            // On Change Klinik
            $(document).on('change', '.clinic', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).clinic = $(this).val()
            })

            // On Change Quantity Came
            $(document).on('change', '.quantityCame', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).quantityCame = $(this).val()

                recountJumlah(id)
                recountTotal()
            })

            // On Change Faktur
            $(document).on('change', '.fakturItem', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).fakturItem = $(this).val()
            })

            // On Change Batch
            $(document).on('change', '.batch', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).batch = $(this).val()
                $('#select2-batch-'+id+'-container').text($(this).val())
            })

            // On Change Expired Date
            $(document).on('change', '.expired', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).expired = $(this).val()
            })

            // On Change Shelf
            $(document).on('change', '.shelf', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).shelf = $(this).val()
                $('#select2-shelf-'+id+'-container').text($(this).val())
            })

            // On Change HNA Each
            $(document).on('change', '.HNAEach', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).HNAEach = $(this).val()

                recountBuyPrice(id)
                recountJumlah(id)
                recountTotal()
            })

            // On Change Discount
            $(document).on('change', '.discount', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).discount = $(this).val()

                recountBuyPrice(id)
                recountJumlah(id)
                recountTotal()
            })

            // On Change Note
            $(document).on('change', '.note', function() {
                let id = $(this).attr('id').split('-')[1]

                inputedItems.find(item => item.id == id).note = $(this).val()
            })

            resetTableFaktur(listFaktur)
            firstAddExistedItems(inputedItems)
            recountTotal()
        })

        function recountTotal(){
            let totalQtyRequest = 0
            let totalQtyCame = 0
            // let totalHNA = 0
            let totalBuyPrice = 0
            let totalAmount = 0

            inputedItems.map(item => {
                totalQtyRequest += parseInt(item.quantityRequest)
                totalQtyCame += parseInt(item.quantityCame)
                // totalHNA += parseInt(item.HNAEach)
                totalBuyPrice += parseInt(item.buyPrice)
                totalAmount += parseInt(item.amount)
            })

            $('#totalQtyRequest').text(totalQtyRequest)
            $('#totalQtyCame').text(totalQtyCame)
            // $('#totalHNA').text(formatNumber(totalHNA))
            $('#totalBuyPrice').text(formatNumber(totalBuyPrice))
            $('#totalAmount').text(formatNumber(totalAmount))
        }

        function firstAddExistedItems(inputedItems){
            inputedItems.map(data => {
                let newRow = `
                                <tr>
                                    <td class="no">
                                        ${++counter}
                                    </td>
                                    <td>
                                        ${data.name}
                                    </td>
                                    <td>
                                        <input type="number" id="quantityRequest-${data.id}" class="form-control quantityRequest" value="${data.quantityRequest}">
                                    </td>
                                    <td>
                                        <input type="text" id="idCRPOBR-${data.id}" class="form-control idCRPOBR" value="${data.idCRPOBR || ''}">
                                    </td>
                                    <td>
                                        <input type="text" id="clinic-${data.id}" class="form-control clinic" value="${data.clinic || ''}">
                                    </td>
                                    <td>
                                        <input type="number" id="quantityCame-${data.id}" class="form-control quantityCame" value="${data.quantityCame || 0}">
                                    </td>
                                    <td>
                                        <select id="faktur-${data.id}" class="form-control fakturItem">
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" id="batch-${data.id}" class="form-control batch">
                                    </td>
                                    <td>
                                        <input type="month" id="expired-${data.id}" class="form-control expired" value="${data.expired || ''}">
                                    </td>
                                    <td>
                                        <input type="text" id="shelf-${data.id}" class="form-control shelf">
                                    </td>
                                    <td>
                                        <input type="number" id="HNAEach-${data.id}" class="form-control HNAEach" value="${data.HNAEach || ''}">
                                    </td>
                                    <td>
                                        <input type="number" id="discount-${data.id}" class="form-control discount" value=${data.discount || 0}>
                                    </td>
                                    <td id="buyPrice-${data.id}">
                                        ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100))}
                                    </td>
                                    <th id="jumlah-${data.id}">
                                        ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100) * parseInt(data.quantityCame))}
                                    </th>
                                    <td>
                                        <input type="text" id="note-${data.id}" class="form-control note" value="${data.note || ''}">
                                    </td>
                                    <td>
                                        <div class="btn btn-danger btn-sm removeButton">
                                            <div style="display:flex; align-items:center;">
                                                <span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `

                $('#dataTable tbody').append(newRow)

                addSelect2Faktur()
                addSelect2Batch(data.id, data.itemId, data.batch || null)
                addSelect2Shelf(data.id, data.shelf, data.shelf || null)

                if(inputedItems.length > 0){
                    $('#supplierId').prop('disabled', 'disabled')
                    $('#type').prop('disabled', 'disabled')
                }
                else{
                    $('#supplierId').prop('disabled', false)
                    $('#type').prop('disabled', false)
                }
            })
        }

        function addSelect2Batch(id, itemId, selected = null){
            $('#batch-'+id).select2({
                    theme: "bootstrap",
                    tags: true,
                    ajax: {
                        url: "{{ route('item.stock.get-item-batch') }}",
                        maximumSelectionLength: 1,
                        data: function (params) {
                            return {
                                search: params.term,
                                partnerId: 1,
                                itemId: itemId
                            }
                        },
                        processResults: function (data) {
                            return data
                        }
                    }
                });

            if(selected){
                $('#batch-'+id).val(selected).trigger('change')
            }
        }

        function addSelect2Shelf(id, itemId, selected = null){
            $('#shelf-'+id).select2({
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
            
            if(selected){
                $('#shelf-'+id).val(selected).trigger('change')
            }
        }

        function addSelect2Faktur(){
            let options = listFaktur.map((faktur) => {
                                return {
                                    id: faktur.id,
                                    text: faktur.faktur.replace('|', '-')
                                }
                            })

            if(options.length > 0){
                options.unshift({
                    id: '',
                    text: '-Pilih Faktur-'
                })
            }

            $('.fakturItem').each(function() {
                let id = $(this).attr('id').split('-')[1];
                let fakturItem = inputedItems.find(x => x.id == id).fakturItem || null
                let fakturId = null

                $('#faktur-' + id).empty();

                $('#faktur-' + id).select2({
                    theme: "bootstrap",
                    data: options
                });

                if(fakturItem != null){
                    fakturId = listFaktur.find(x => x.id == fakturItem).id
                    $('#faktur-' + id).val(fakturId).trigger('change');
                }
            });
        }

        function resetTableFaktur(listFaktur){
            $('#fakturTable tbody').html('');

            listFaktur.map((data, index) => {
                let newRow = $('<tr><td>' + (parseInt(index) + 1) + '</td><td>' + data.faktur.split('|')[0].trim() + '</td><td>' + data.faktur.split('|')[1].trim() + '<td><div class="btn btn-danger btn-sm removeFakturButton" id="fakturBtn-' + data.id + '"><div style="display:flex; align-items:center;"><span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span></div></div></td></tr>')

                $('#fakturTable tbody').append(newRow)
            })

            addSelect2Faktur()
        }

        function resetTable(inputedItems){
            $('#dataTable tbody').html('');

            inputedItems.map((data, index) => {
                let newRow = $('<tr><td>' + (parseInt(index) + 1) + '</td><td>' + data.nameModified + '</td><td>' + data.quantity + '</td><td>' + formatNumber(parseInt(data.price)) + '</td><td><b>' +  formatNumber(data.price * data.quantity) + '</b></td><td><div class="btn btn-danger btn-sm removeButton"><div style="display:flex; align-items:center;"><span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span></div></div></td></tr>')

                $('#dataTable tbody').append(newRow)
            })
        }

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

        function updateTotalPrice(inputedItems){
            let totalPrice = 0

            inputedItems.map((x) => {
                totalPrice += parseInt(x.price) * x.quantity
            })

            return totalPrice
        }

        function recountBuyPrice(id){
            let item = inputedItems.find(x => x.id == id)
            let buyPrice = parseInt(item.HNAEach * (100 - item.discount) / 100)
            item.buyPrice = buyPrice

            $('#buyPrice-'+id).text(formatNumber(buyPrice))
        }

        function recountJumlah(id){
            let item = inputedItems.find(x => x.id == id)
            let jumlah = parseInt(item.HNAEach * (100 - item.discount) / 100) * parseInt(item.quantityCame)
            item.amount = jumlah

            $('#jumlah-'+id).text(formatNumber(jumlah))
        }

        function checkInsertFaktur(faktur){
            if(!listFaktur.some(x => x.faktur == faktur)){
                let id = listFaktur.length == 0 ? 1 : listFaktur[listFaktur.length - 1].id + 1

                listFaktur.push({
                    id: id,
                    faktur: faktur
                })

                resetTableFaktur(listFaktur)

                return id
            }
            else{
                return listFaktur.find(x => x.faktur == faktur).id
            }
        }

        $('#supplierId').select2({theme: "bootstrap"})

        $('#itemId').select2({
                    theme: "bootstrap",
                    ajax: {
                        url: "{{ route('buy.edit', ['buy' => $buy->id]) }}",
                        maximumSelectionLength: 1,
                        data: function (params) {
                            return {
                                search: params.term,
                                supplierId: $('#supplierId').val(),
                                type: $('#type').val(),
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
                $('#addItem').show()
            }
            else{
                $('#addItem').hide()
            }
        })

        $('#quantity').on('change', function() {
            if($('#itemId').val() != null && $(this).val() > 0){
                $('#addItem').show()
            }
            else{
                $('#addItem').hide()
            }
        })

        $('#supplierId').on('change', function() {
            $('#itemId').val(null).trigger('change')
            $('#addItem').hide()

            currentItems = {}
        })

    </script>
@endsection
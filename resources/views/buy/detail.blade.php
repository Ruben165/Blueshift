@extends('layouts.app')

@section('title', 'Detail Pembelian')

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
        <h1>List Item {{ $buy->SP_no }}</h1>
        <div class="right_header">
            {{-- <a href="{{ route('buy.export-excel', ['buy' => $buy->id ]) }}" class="btn btn-success mb-3">
                <span class="fas fa-file-excel mr-2"></span>Export Excel
            </a>
            <a href="{{ route('buy.export-pdf', ['buy' => $buy->id ]) }}" target="_blank" class="btn btn-danger mb-3 ml-2">
                <span class="fas fa-file-pdf mr-2"></span>Export SP
            </a>
            <a href="{{ asset('storage/' . $buy->path) }}" target="_blank" class="btn btn-info mb-3 ml-2">
                <span class="fas fa-file-pdf mr-2"></span>Surat Approval
            </a> --}}
            @if($buy->status_id == 2)
            <a href="{{ route('buy.print-barcode', ['buy' => $buy->id ]) }}" class="btn btn-primary mb-3 ml-2">
                <span class="fas fa-print mr-2"></span>Print Barcode
            </a>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <table>
                <tr>
                    <th>Tanggal SP</th>
                    <td>: {{ $buy->SP_date ? Carbon\Carbon::parse($buy->SP_date)->format('d-m-Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Approve</th>
                    <td>: {{ $buy->approve_date ? Carbon\Carbon::parse($buy->approve_date)->format('d-m-Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Kirim</th>
                    <td>: {{ $buy->send_date ? Carbon\Carbon::parse($buy->send_date)->format('d-m-Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Terima</th>
                    <td>: {{ $buy->receive_date ? Carbon\Carbon::parse($buy->receive_date)->format('d-m-Y') : '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="pbf">PBF:</label>
                <input type="text" name="pbf" id="pbf" class="form-control" value="{{ $buy->supplier->name }}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="jenisObat">Jenis Obat:</label>
                <input type="text" name="jenisObat" id="jenisObat" class="form-control" value="{{ $buy->type->name }}" disabled>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @include('buy.__item-table')
        </div>
        @if($buy->status_id == 1)
        <div class="col-md-12 mt-3 mb-3">
            <form method="POST" action="{{ route('buy.terima-pesanan', ['buy' => $buy->id]) }}" id='theForm'>
            @csrf
                <button id="terimaPesanan" class="btn btn-primary">
                    <span class="fas fa-check mr-2"></span>Terima Pesanan
                </button>
            </form>
        </div>
        @endif
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
        let listFaktur = {!! json_encode($buy->faktur) !!};
        let inputedItems = {!! json_encode($listItems) !!};
        let counter = 0;

        $(document).ready(function() {
            $('#terimaPesanan').on('click', function(e){
                e.preventDefault();

                let buyOrder = {!! $buy->toJson() !!}
                let valueNull = false

                valueNull = inputedItems.some(item => {
                    return item.batch == null || item.clinic == null || item.expired == null || item.idCRPOBR == null || item.shelf == null || item.fakturItem == null
                })

                if([null].includes(buyOrder.SP_date, buyOrder.approve_date, buyOrder.send_date, buyOrder.receiveDate)){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan seluruh kolom tanggal di menu edit sebelum submit!',
                        'error'
                    );
                }
                else if(valueNull){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan seluruh input pada item di menu edit sebelum submit!',
                        'error'
                    );
                }
                else{
                    swal.fire({
                        title: 'Apakah anda yakin?',
                        text: "Stock pusat akan bertambah setelah anda menyelesaikan Pembelian ini!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#218838',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Selesaikan Pembelian',
                        reverseButtons: true
                        }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).hide()
                            $('#theForm').submit()
                        }
                    });
                }
            })

            firstAddExistedItems(inputedItems)
            countTotal()
            addSelect2Faktur()
        });

        function countTotal(){
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
                            `

                if(`{{ $buy->status_id }}` == '2'){
                    newRow += `
                            <td>
                                ${data.barcode_id != null ? data.barcode_id : '-'}
                            </td>
                    `
                }

                newRow += `
                                    <td>
                                        ${data.name}
                                    </td>
                                    <td>
                                        <input type="number" id="quantityRequest-${data.id}" class="form-control quantityRequest" value="${data.quantityRequest}" disabled>
                                    </td>
                                    <td>
                                        <input type="text" id="idCRPOBR-${data.id}" class="form-control idCRPOBR" value="${data.idCRPOBR || ''}" disabled>
                                    </td>
                                    <td>
                                        <input type="text" id="clinic-${data.id}" class="form-control clinic" value="${data.clinic || ''}" disabled>
                                    </td>
                                    <td>
                                        <input type="number" id="quantityCame-${data.id}" class="form-control quantityCame" value="${data.quantityCame || 0}" disabled>
                                    </td>
                                    <td>
                                        <select id="faktur-${data.id}" class="form-control fakturItem" disabled>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" id="batch-${data.id}" class="form-control batch" value="${data.batch || ''}" disabled>
                                    </td>
                                    <td>
                                        <input type="month" id="expired-${data.id}" class="form-control expired" value="${data.expired || ''}" disabled>
                                    </td>
                                    <td>
                                        <input type="text" id="shelf-${data.id}" class="form-control shelf" value="${data.shelf || ''}" disabled>
                                    </td>
                                    <td>
                                        <input type="number" id="HNAEach-${data.id}" class="form-control HNAEach" value="${data.HNAEach || ''}" disabled>
                                    </td>
                                    <td>
                                        <input type="number" id="discount-${data.id}" class="form-control discount" value=${data.discount || 0} disabled>
                                    </td>
                                    <td id="buyPrice-${data.id}">
                                        ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100))}
                                    </td>
                                    <th id="jumlah-${data.id}">
                                        ${formatNumber(parseInt(data.HNAEach * (100 - data.discount) / 100) * parseInt(data.quantityCame))}
                                    </th>
                                    <td>
                                        <input type="text" id="note-${data.id}" class="form-control note" value="${data.note || ''}" disabled>
                                    </td>
                                </tr>
                            `

                $('#dataTable tbody').append(newRow)

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
                    text: '-'
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

        function formatNumber(number){
            return number.toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR'
            })
        }
    </script>
@endsection
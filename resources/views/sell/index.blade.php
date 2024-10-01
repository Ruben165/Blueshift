@extends('layouts.app')

@if($type == 'Reguler')
@section('title', 'List Penjualan Reguler')
@elseif($type == 'Konsinyasi')
@section('title', 'List Konsinyasi')
@elseif($type == 'Transfer')
@section('title', 'List Transfer')
@elseif($type == 'Retur')
@section('title', 'List Retur')
@else
@section('title', 'List Stock Opname')
@endif

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
        @if($type != 'Konsinyasi')
            <h1>List {{ $type == 'Reguler' ? 'Penjualan' : '' }} {{ $type }}</h1>
        @else
            <h1>List Pengiriman Konsinyasi</h1>
        @endif
        <div>
            <button class="btn btn-success mb-3 mr-2" id="exportDetail">
                <span class="fas fa-print mr-2"></span>Export Detail List {{ $type == 'Reguler' ? 'Penjualan' : '' }} {{ $type }}
            </button>
            @if($type == 'Reguler' || $type == 'Konsinyasi')
                <a href="{{ route('sell.create', ['type' => $type]) }}" class="btn btn-primary mb-3">
                    <span class="fas fa-plus mr-2"></span>Tambah {{ $type == 'Reguler' ? 'Penjualan' : '' }} {{ $type }}
                </a>
            @elseif($type != 'SO')
                <a href="#" class="btn btn-primary mb-3" id="pilihMitraAsal">
                    <span class="fas fa-plus mr-2"></span>Tambah {{ $type == 'Reguler' ? 'Penjualan' : '' }} {{ $type }}
                </a>
            @else
                <button class="btn btn-success mb-3 mr-2" id="sisaStock">
                    <span class="fas fa-print mr-2"></span>Export List Konsinyasi
                </button>
                <button class="btn btn-primary mb-3" id="SOButton">
                    <span class="fas fa-plus mr-2"></span>Tambah SO
                </button>
            @endif
        </div>
    </div>
@endsection

@section('content')
    @include('sell.__table')
    <div class="modal hide fade" tabindex="-1" id="modalPrintHarga">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Harga</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sell.konsinyasi.export-harga') }}" method="post" target="_blank">
                @csrf
                    <input type="hidden" name="id" id="sellIdForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="allOrNot">Pilih Data:</label>
                            <select name="allOrNot" id="allOrNot" class="form-control">
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jenisExport">Pilih Tipe Export:</label>
                            <select name="jenisExport" id="jenisExport" class="form-control">
                                <option value="PDF">PDF</option>
                                <option value="Excel">Excel</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalTambahSO">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah SO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sell.so') }}" method="post">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="partnerId">Pilih Mitra Konsinyasi:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <select name="partnerId" id="partnerId" class="form-control">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalSisaStock">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export List Konsinyasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sell.so.export-sisa-stock') }}" method="post" target="_blank">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="partnerIdPrint">Pilih Mitra Konsinyasi:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <select name="partnerIdPrint" id="partnerIdPrint" class="form-control">
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="jenisExport">Pilih Tipe Export:</label>
                                    <select name="jenisExport" id="jenisExport" class="form-control">
                                        <option value="PDF">PDF</option>
                                        <option value="Excel">Excel</option>
                                        <option value="CSV">CSV</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalCurrentStock">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Stock Mitra</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sell.export-stock-retur') }}" method="post" target="_blank">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="partnerIdRetur">Pilih Mitra:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <select name="partnerIdRetur" id="partnerIdRetur" class="form-control">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalPrintSisaSO">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Sisa SO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sell.print-hasil-so') }}" method="post" target="_blank">
                @csrf
                    <input type="hidden" name="id" id="sellIdFormSO">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="jenisExportSO">Pilih Tipe Export:</label>
                            <select name="jenisExportSO" id="jenisExportSO" class="form-control">
                                <option value="PDF">PDF</option>
                                <option value="Excel">Excel</option>
                                <option value="CSV">CSV</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalPilihMitraAsal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Pilih Mitra Asal</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="{{ route('sell.create.post', ['type' => $type]) }}" method="post">
            @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="partnerSourceId">Pilih Mitra Sumber</label>
                        <div style="width: 100%" id="partnerIdContainer">
                            <select class="form-control" id="partnerSourceId" name="partnerSourceId">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
          </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalPICCancel">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Masukkan Alasan Cancel</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="" id="cancelForm" method="post" enctype="multipart/form-data">
            @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="picCancel">Nama PIC</label>
                        <input required type="text" class="form-control" id="picCancel" name="picCancel" placeholder="Masukkan nama PIC...">
                    </div>
                    <div class="form-group">
                        <label for="alasan">Alasan Cancel</label>
                        <textarea required class="form-control" id="alasan" name="alasan" placeholder="Masukkan alasan cancel..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="buktiCancel">Bukti Cancel</label>
                        <input type="file" class="form-control" id="buktiCancel" name="buktiCancel">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Cancel</button>
                </div>
            </form>
          </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalExportDetail">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Detail List</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sell.export-list-detail') }}" method="post" target="_blank">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="daterange">Pilih Jenis Tanggal:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <select name="datetype" id="datetype" class="form-control" required>
                                            <option value="created" selected>Tanggal Pemesanan</option>
                                            @if($type == 'Retur')
                                            <option value="delivered">Tanggal Retur</option>
                                            @else
                                            <option value="delivered">Tanggal Pengiriman</option>
                                            @endif
                                        </select>
                                    </div><br>
                                    <label for="daterange">Pilih Rentang Tanggal:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        <input type="hidden" name="isSellOrder" value="true">
                                        <input type="text" name="daterange" value="" class="form-control"/>
                                    </div><br>
                                    <strong class="text-red">Perhatian:</strong><br>
                                    <span class="text-red">- Maksimal rentang yang dapat dipilih adalah 1 bulan!</span><br>
                                    <span class="text-red">- Maksimal data yang dapat dipilih adalah data  6 bulan terakhir!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
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

        .btn-warning {
            color: #fff;
            background-color: #e0a905; 
            border-color: #e0a905;
        }

        .btn-warning:hover {
            color: #fff;
        }

        #partnerIdContainer .select2-container{
            width: 100% !important;
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

        #partnerIdController .select2-container{
            width: 100% !important;
        }

        #sell-list.table.table-bordered.table-hover.dataTable{
            margin: 0;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">
        $(document).ready(function() {
            //Inisialisasi Database
            if('{{ $type }}' == 'Reguler'){
                initSellDatatableWithInvoice();
            }
            else if('{{ $type }}' == 'SO'){
                initSellDatatableSO();
            }
            else if('{{ $type }}' != 'Konsinyasi'){
                if('{{ $type }}' == 'Retur'){
                    initSellDatatable('Retur');
                }
                else{
                    initSellDatatable('Transfer');
                }
            }
            else{
                initSellDatatableKonsinyasi();
            }

            $(document).on('click', '.Detail', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Edit', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.EditItem', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            // $(document).on('click', '.SO', function(){
            //     let routeUrl = $(this).attr("href")
            //     window.location.href = routeUrl
            // })

            // $(document).on('click', '.Restock', function(){
            //     let routeUrl = $(this).attr("href")
            //     window.location.href = routeUrl
            // })

            $(document).on('click', '.Export', function() {    
                $('#sellIdForm').val($(this).attr('buttonid'))

                let newOptions = [
                    { value: "All", text: "Semua Data" },
                    { value: "Not All", text: $(this).attr('consigntype') },
                ]
                
                $('#allOrNot').empty()

                $.each(newOptions, function(index, option) {
                    $('#allOrNot').append($('<option>').attr('value', option.value).text(option.text));
                })

                $('#modalPrintHarga').modal({
                    show: true
                })
            })

            $(document).on('click', '.ExportSisa', function() {    
                $('#sellIdFormSO').val($(this).attr('buttonid'))

                $('#modalPrintSisaSO').modal({
                    show: true
                })
            })

            $(document).on('click', '#SOButton', function() {
                $('#modalTambahSO').modal({
                    show: true
                })
            })

            $(document).on('click', '#sisaStock', function() {
                $('#modalSisaStock').modal({
                    show: true
                })
            })

            $(document).on('click', '#currentStock', function() {
                $('#modalCurrentStock').modal({
                    show: true
                })
            })

            $(document).on('click', '#exportDetail', function() {
                $('#modalExportDetail').modal({
                    show: true
                })
            })

            $('input[name="daterange"]').daterangepicker({
                startDate: moment().subtract(1, "months"),
                endDate: moment(),
                minDate: moment().subtract(6, "months").startOf("month"),
                maxDate: moment(),
                maxSpan: {
                    "months" : 1
                },
                autoUpdateInput: true,
                autoApply: true,
                locale: {
                    "format": "DD/MM/YYYY"
                }
            });

            $(document).on('click', '.Cancel', function(){
                // if(!'{{ $type }}' == 'Reguler'){
                //     swal.fire({
                //         title: 'Apakah anda yakin?',
                //         text: "Semua data penjualan yang ada di data ini akan dibatalkan!",
                //         icon: 'warning',
                //         showCancelButton: true,
                //         confirmButtonColor: '#d33',
                //         cancelButtonColor: '#3085d6',
                //         confirmButtonText: 'Ganti Status',
                //         reverseButtons: true
                //     }).then((result) => {
                //         if (result.isConfirmed) {
                //             let routeUrl = $(this).attr("href")
                //             window.location.href = routeUrl
                //         }
                //     });
                // }
                // else{
                    $('#cancelForm').attr('action', $(this).attr("href"))

                    $('#modalPICCancel').modal({
                        show: true
                    })
                // }
            })

            $(document).on('click', '.Edit', function(){
                swal.fire({
                    title: 'Ubah Jadwal SO',
                    text: "Due date akan diganti!",
                    html: '<div class="form-group"><input id="swal-input" type="date" class="form-control"></div>',
                    showCancelButton: true,
                    confirmButtonColor: '#138496',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ganti Jadwal',
                    reverseButtons: true
                    }).then((result) => {
                    if (result.isConfirmed) {
                        const baseUrl = window.location.origin;
                        let theRoute = baseUrl + '/admin/sell/' + $(this).attr('buttonId') + '/changeDue/' + encodeURIComponent($('#swal-input').val());
                        window.location.href = theRoute
                    }
                });
            })
        });

        function initSellDatatable(type){
            if(type == 'Transfer'){
                let table = $('#sell-list').DataTable({
                    "scrollX": true,
                    "processing": true,
                    "serverSide": true,
                    "ajax": "{{ route($route, ['type' => $type]) }}",
                    "columnDefs": [
                        {
                            "data": null,
                            "targets": 0
                        },
                        {
                            "data": "sell_order_type_id",
                            "name": "sell_order_type_id",
                            "targets": 1
                        },
                        {
                            "data": "source_partner_id",
                            "name": "source_partner_id",
                            "targets": 2
                        },
                        {
                            "data": "destination_partner_id",
                            "name": "destination_partner_id",
                            "targets": 3
                        },
                        {
                            "data": "document_number",
                            "name": "document_number",
                            "targets": 4,
                        },
                        {
                            "data": "created_at",
                            "name": "created_at.timestamp",
                            "render": function(data, type, row, meta) {
                                return row.created_at.display
                            },
                            "targets": 5,
                        },
                        {
                            "data": "total_price",
                            "name": "total_price",
                            "targets": 6,
                        },
                        {
                            "data": "delivered_at",
                            "name": "delivered_at.timestamp",
                            "render": function(data, type, row, meta) {
                                return row.delivered_at.display
                            },
                            "targets": 7,
                        },
                        {
                            "data": "status_id",
                            "name": "status_id",
                            "targets": 8
                        },
                        {
                            "data": 'actions',
                            "targets": 9,
                            "render": function(data, type, row, meta) {
                                if (data !== '') {
                                    let actionContent = `<div style='display: flex; gap:0.5em;'>`;
    
                                    data.map((button, idx) => {
                                        actionContent += 
                                        `<button href="${button.route}" buttonId="${button.attr_id}" class="btn btn-${button.btnStyle} btn-sm ${button.btnClass}">
                                                <div style="display:flex; align-items:center;">
                                                    <span class="${button.icon}"></span>
                                                    <span style="margin-left: 0.25em; text-wrap: nowrap;">${button.label}</span>
                                                </div>
                                        </button>`;
                                    })
    
                                    actionContent += `</div>`
    
                                    return actionContent;
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            "searchable": false,
                            "orderable": false,
                            "targets": [9]
                        }
                    ],
                    search: {
                        smart: false,
                        "caseInsensitive": false,
                        return: true
                    },
                    "createdRow": function(row, data, dataIndex) {
                        $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                    },
                    initComplete: function() {
                        this.api()
                            .columns()
                            .every(function(i) {
                                columnArr = ['destination_partner_id', 'status_id']
    
                                var columns = table.settings().init().columnDefs;
                                var _changeInterval = null;
                                let name = columns[i].name
    
                                if (columnArr.includes(name)) {
                                    var column = this;
    
                                    if (name == 'destination_partner_id') {
                                        let div = $(`<div id="wrap-partner" class="form-group"></div>`).appendTo($(column.footer()).empty())
    
                                        var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Mitra</option></select>`)
                                            .appendTo($('#wrap-partner'))
                                            .on('change', function() {
                                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
    
                                                column.search(val, false, false).draw();
                                            });
    
    
                                            div.append(select)
    
                                            if(type != 'Retur'){
                                                $("#select_partner").select2({
                                                    theme: "bootstrap",
                                                    ajax: {
                                                        url: "{{ route('mitra.getMitra') }}",
                                                        maximumSelectionLength: 1,
                                                        data: function (params) {
                                                            return {
                                                                exclude: [1],
                                                                forDataTable: true,
                                                                search: params.term
                                                            }
                                                        },
                                                        processResults: function (data) {
                                                            return data
                                                        }
                                                    },
                                                })
                                            }
                                            else{
                                                $("#select_partner").select2({
                                                    theme: "bootstrap",
                                                    ajax: {
                                                        url: "{{ route('mitra.getMitra') }}",
                                                        maximumSelectionLength: 1,
                                                        data: function (params) {
                                                            return {
                                                                include: [1],
                                                                forDataTable: true,
                                                                search: params.term
                                                            }
                                                        },
                                                        processResults: function (data) {
                                                            return data
                                                        }
                                                    },
                                                })
                                            }
                                    }
                                    
                                    if (name == 'status_id') {
                                        let statusList = {
                                            'Process': 'Process',
                                            'Receipted': 'Receipted',
                                            'Cancel': 'Cancel',
                                        }
    
                                        let div = $(`<div id="wrap-status" class="form-group"></div>`).appendTo($(column.footer()).empty())
    
                                        var select = $(`<select id="select_status" class="form-control"><option value="" selected>Pilih Status</option></select>`)
                                            .appendTo($('#wrap-status'))
                                            .on('change', function() {
                                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
    
                                                column.search(val, false, false).draw();
                                            });
    
    
                                        for (var key in statusList) {
                                            $('#select_status').append(`<option value="${key}">${statusList[key]}</option>`);
                                        }
    
                                        div.append(select)
                                        $("#select_status").select2({theme: "bootstrap"})
                                    }
                                }
                            });
                    },
                })
            }
            else{
                let table = $('#sell-list').DataTable({
                    "scrollX": true,
                    "processing": true,
                    "serverSide": true,
                    "ajax": "{{ route($route, ['type' => $type]) }}",
                    "columnDefs": [
                        {
                            "data": null,
                            "targets": 0
                        },
                        {
                            "data": "sell_order_type_id",
                            "name": "sell_order_type_id",
                            "targets": 1
                        },
                        {
                            "data": "source_partner_id",
                            "name": "source_partner_id",
                            "targets": 2
                        },
                        {
                            "data": "document_number",
                            "name": "document_number",
                            "targets": 3,
                        },
                        {
                            "data": "returned_at",
                            "name": "returned_at.timestamp",
                            "render": function(data, type, row, meta) {
                                return row.returned_at.display
                            },
                            "targets": 4,
                        },
                        {
                            "data": "total_qty",
                            "name": "total_qty",
                            "targets": 5,
                        },
                        {
                            "data": "total_price",
                            "name": "total_price",
                            "targets": 6,
                        },
                        {
                            "data": "status_id",
                            "name": "status_id",
                            "targets": 7
                        },
                        {
                            "data": 'actions',
                            "targets": 8,
                            "render": function(data, type, row, meta) {
                                if (data !== '') {
                                    let actionContent = `<div style='display: flex; gap:0.5em;'>`;
    
                                    data.map((button, idx) => {
                                        actionContent += 
                                        `<button href="${button.route}" buttonId="${button.attr_id}" class="btn btn-${button.btnStyle} btn-sm ${button.btnClass}">
                                                <div style="display:flex; align-items:center;">
                                                    <span class="${button.icon}"></span>
                                                    <span style="margin-left: 0.25em; text-wrap: nowrap;">${button.label}</span>
                                                </div>
                                        </button>`;
                                    })
    
                                    actionContent += `</div>`
    
                                    return actionContent;
                                } else {
                                    return '';
                                }
                            }
                        },
                        {
                            "searchable": false,
                            "orderable": false,
                            "targets": [8]
                        }
                    ],
                    search: {
                        smart: false,
                        "caseInsensitive": false,
                        return: true
                    },
                    "createdRow": function(row, data, dataIndex) {
                        $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                    },
                    initComplete: function() {
                        this.api()
                            .columns()
                            .every(function(i) {
                                columnArr = ['destination_partner_id', 'status_id']
    
                                var columns = table.settings().init().columnDefs;
                                var _changeInterval = null;
                                let name = columns[i].name
    
                                if (columnArr.includes(name)) {
                                    var column = this;
    
                                    if (name == 'destination_partner_id') {
                                        let div = $(`<div id="wrap-partner" class="form-group"></div>`).appendTo($(column.footer()).empty())
    
                                        var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Mitra</option></select>`)
                                            .appendTo($('#wrap-partner'))
                                            .on('change', function() {
                                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
    
                                                column.search(val, false, false).draw();
                                            });
    
    
                                            div.append(select)
    
                                            if(type != 'Retur'){
                                                $("#select_partner").select2({
                                                    theme: "bootstrap",
                                                    ajax: {
                                                        url: "{{ route('mitra.getMitra') }}",
                                                        maximumSelectionLength: 1,
                                                        data: function (params) {
                                                            return {
                                                                exclude: [1],
                                                                forDataTable: true,
                                                                search: params.term
                                                            }
                                                        },
                                                        processResults: function (data) {
                                                            return data
                                                        }
                                                    },
                                                })
                                            }
                                            else{
                                                $("#select_partner").select2({
                                                    theme: "bootstrap",
                                                    ajax: {
                                                        url: "{{ route('mitra.getMitra') }}",
                                                        maximumSelectionLength: 1,
                                                        data: function (params) {
                                                            return {
                                                                include: [1],
                                                                forDataTable: true,
                                                                search: params.term
                                                            }
                                                        },
                                                        processResults: function (data) {
                                                            return data
                                                        }
                                                    },
                                                })
                                            }
                                    }
                                    
                                    if (name == 'status_id') {
                                        let statusList = {
                                            'Process': 'Process',
                                            'Receipted': 'Receipted',
                                            'Cancel': 'Cancel',
                                        }
    
                                        let div = $(`<div id="wrap-status" class="form-group"></div>`).appendTo($(column.footer()).empty())
    
                                        var select = $(`<select id="select_status" class="form-control"><option value="" selected>Pilih Status</option></select>`)
                                            .appendTo($('#wrap-status'))
                                            .on('change', function() {
                                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
    
                                                column.search(val, false, false).draw();
                                            });
    
    
                                        for (var key in statusList) {
                                            $('#select_status').append(`<option value="${key}">${statusList[key]}</option>`);
                                        }
    
                                        div.append(select)
                                        $("#select_status").select2({theme: "bootstrap"})
                                    }
                                }
                            });
                    },
                })
            }


            // Add an event listener to the search input field
            $('#sell-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }

        function initSellDatatableWithInvoice(){
            let table = $('#sell-list').DataTable({
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route, ['type' => $type]) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "sell_order_type_id",
                        "name": "sell_order_type_id",
                        "targets": 1
                    },
                    {
                        "data": "destination_partner_id",
                        "name": "destination_partner_id",
                        "targets": 2
                    },
                    {
                        "data": "created_at",
                        "name": "created_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.created_at.display
                        },
                        "targets": 3,
                    },
                    {
                        "data": "total_price",
                        "name": "total_price",
                        "targets": 4,
                    },
                    {
                        "data": "delivered_at",
                        "name": "delivered_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.delivered_at.display
                        },
                        "targets": 5,
                    },
                    {
                        "data": "path",
                        "name": "path",
                        "targets": 6,
                    },
                    {
                        "data": "status_id",
                        "name": "status_id",
                        "targets": 7
                    },
                    {
                        "data": 'actions',
                        "targets": 8,
                        "render": function(data, type, row, meta) {
                            if (data !== '') {
                                let actionContent = `<div style='display: flex; gap:0.5em;'>`;

                                data.map((button, idx) => {
                                    actionContent += 
                                    `<button href="${button.route}" buttonId="${button.attr_id}" class="btn btn-${button.btnStyle} btn-sm ${button.btnClass}">
                                            <div style="display:flex; align-items:center;">
                                                <span class="${button.icon}"></span>
                                                <span style="margin-left: 0.25em; text-wrap: nowrap;">${button.label}</span>
                                            </div>
                                    </button>`;
                                })

                                actionContent += `</div>`

                                return actionContent;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": [8]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false,
                    return: true
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                },
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function(i) {
                            columnArr = ['destination_partner_id', 'status_id']

                            var columns = table.settings().init().columnDefs;
                            var _changeInterval = null;
                            let name = columns[i].name

                            if (columnArr.includes(name)) {
                                var column = this;

                                if (name == 'destination_partner_id') {
                                    let partnerLists = {!! $listMitra->toJson() !!}

                                    let div = $(`<div id="wrap-partner" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Mitra</option></select>`)
                                        .appendTo($('#wrap-partner'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                        div.append(select)

                                        $("#select_partner").select2({
                                            theme: "bootstrap",
                                            ajax: {
                                                url: "{{ route('mitra.getMitra') }}",
                                                maximumSelectionLength: 1,
                                                data: function (params) {
                                                    return {
                                                        exclude: [1],
                                                        forDataTable: true,
                                                        search: params.term
                                                    }
                                                },
                                                processResults: function (data) {
                                                    return data
                                                }
                                            },
                                        })
                                }
                                
                                if (name == 'status_id') {
                                    let statusList = {
                                        'Process': 'Process',
                                        'Receipted': 'Receipted',
                                        'Cancel': 'Cancel',
                                    }

                                    let div = $(`<div id="wrap-status" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_status" class="form-control"><option value="" selected>Pilih Status</option></select>`)
                                        .appendTo($('#wrap-status'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                    for (var key in statusList) {
                                        $('#select_status').append(`<option value="${key}">${statusList[key]}</option>`);
                                    }

                                    div.append(select)
                                    $("#select_status").select2({theme: "bootstrap"})
                                }
                            }
                        });
                },
            })

            // Add an event listener to the search input field
            $('#sell-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }

        function initSellDatatableSO(){
            let table = $('#sell-list').DataTable({
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route, ['type' => $type]) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "sell_order_type_id",
                        "name": "sell_order_type_id",
                        "targets": 1
                    },
                    {
                        "data": "destination_partner_id",
                        "name": "destination_partner_id",
                        "targets": 2
                    },
                    {
                        "data": "document_number",
                        "name": "document_number",
                        "targets": 3,
                    },
                    {
                        "data": "created_at",
                        "name": "created_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.created_at.display
                        },
                        "targets": 4,
                    },
                    {
                        "data": "total_price",
                        "name": "total_price",
                        "targets": 5,
                    },
                    {
                        "data": "delivered_at",
                        "name": "delivered_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.delivered_at.display
                        },
                        "targets": 6,
                    },
                    {
                        "data": "path",
                        "name": "path",
                        "targets": 7,
                    },
                    {
                        "data": "status_id",
                        "name": "status_id",
                        "targets": 8
                    },
                    {
                        "data": 'actions',
                        "targets": 9,
                        "render": function(data, type, row, meta) {
                            if (data !== '') {
                                let actionContent = `<div style='display: flex; gap:0.5em;'>`;

                                data.map((button, idx) => {
                                    actionContent += 
                                    `<button href="${button.route}" buttonId="${button.attr_id}" class="btn btn-${button.btnStyle} btn-sm ${button.btnClass}">
                                            <div style="display:flex; align-items:center;">
                                                <span class="${button.icon}"></span>
                                                <span style="margin-left: 0.25em; text-wrap: nowrap;">${button.label}</span>
                                            </div>
                                    </button>`;
                                })

                                actionContent += `</div>`

                                return actionContent;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": [9]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false,
                    return: true
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                },
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function(i) {
                            columnArr = ['destination_partner_id', 'status_id']

                            var columns = table.settings().init().columnDefs;
                            var _changeInterval = null;
                            let name = columns[i].name

                            if (columnArr.includes(name)) {
                                var column = this;

                                if (name == 'destination_partner_id') {
                                    let partnerLists = {!! $listMitra->toJson() !!}

                                    let div = $(`<div id="wrap-partner" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Mitra</option></select>`)
                                        .appendTo($('#wrap-partner'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                        div.append(select)

                                        $("#select_partner").select2({
                                            theme: "bootstrap",
                                            ajax: {
                                                url: "{{ route('mitra.getMitra') }}",
                                                maximumSelectionLength: 1,
                                                data: function (params) {
                                                    return {
                                                        exclude: [1],
                                                        forDataTable: true,
                                                        search: params.term
                                                    }
                                                },
                                                processResults: function (data) {
                                                    return data
                                                }
                                            },
                                        })
                                }
                                
                                if (name == 'status_id') {
                                    let statusList = {
                                        'Process': 'Process',
                                        'Receipted': 'Receipted',
                                        'Cancel': 'Cancel',
                                    }

                                    let div = $(`<div id="wrap-status" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_status" class="form-control"><option value="" selected>Pilih Status</option></select>`)
                                        .appendTo($('#wrap-status'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                    for (var key in statusList) {
                                        $('#select_status').append(`<option value="${key}">${statusList[key]}</option>`);
                                    }

                                    div.append(select)
                                    $("#select_status").select2({theme: "bootstrap"})
                                }
                            }
                        });
                },
            })

            // Add an event listener to the search input field
            $('#sell-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }

        function initSellDatatableKonsinyasi(){
            let table = $('#sell-list').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route, ['type' => $type]) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "sell_order_type_id",
                        "name": "sell_order_type_id",
                        "targets": 1
                    },
                    {
                        "data": "destination_partner_id",
                        "name": "destination_partner_id",
                        "targets": 2
                    },
                    {
                        "data": "batch_name",
                        "name": "batch_name",
                        "targets": 3
                    },
                    {
                        "data": "created_at",
                        "name": "created_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.created_at.display
                        },
                        "targets": 4,
                    },
                    {
                        "data": "total_price",
                        "name": "total_price",
                        "targets": 5,
                    },
                    {
                        "data": "delivered_at",
                        "name": "delivered_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.delivered_at.display
                        },
                        "targets": 6,
                    },
                    {
                        "data": "due_at",
                        "name": "due_at.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.due_at.display
                        },
                        "targets": 7,
                    },
                    {
                        "data": "status_id",
                        "name": "status_id",
                        "targets": 8
                    },
                    {
                        "data": 'actions',
                        "targets": 9,
                        "render": function(data, type, row, meta) {
                            if (data !== '') {
                                let actionContent = `<div style='display: flex; gap:0.5em;'>`;

                                data.map((button, idx) => {
                                    actionContent += 
                                    `<button href="${button.route || '#'}" buttonId="${button.attr_id}" consignType="${button.consingmentType || ''}" class="btn btn-${button.btnStyle} btn-sm ${button.btnClass}">
                                            <div style="display:flex; align-items:center;">
                                                <span class="${button.icon}"></span>
                                                <span style="margin-left: 0.25em; text-wrap: nowrap;">${button.label}</span>
                                            </div>
                                    </button>`;
                                })

                                actionContent += `</div>`

                                return actionContent;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": [9]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false,
                    return: true
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                },
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function(i) {
                            columnArr = ['destination_partner_id', 'status_id', 'batch_name']

                            var columns = table.settings().init().columnDefs;
                            var _changeInterval = null;
                            let name = columns[i].name

                            if (columnArr.includes(name)) {
                                var column = this;

                                if (name == 'destination_partner_id') {
                                    let div = $(`<div id="wrap-partner" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Klinik Tujuan...</option></select>`)
                                        .appendTo($('#wrap-partner'))
                                        .on('change', function() {
                                            var val = $(this).val();

                                            column.search(val, false, false).draw();
                                        });


                                    div.append(select)

                                    $("#select_partner").select2({
                                        theme: "bootstrap",
                                        ajax: {
                                            url: "{{ route('mitra.getMitra') }}",
                                            maximumSelectionLength: 1,
                                            data: function (params) {
                                                return {
                                                    exclude: [1],
                                                    forDataTable: true,
                                                    search: params.term
                                                }
                                            },
                                            processResults: function (data) {
                                                return data
                                            }
                                        },
                                    })
                                }
                                
                                if (name == 'status_id') {
                                    let statusList = {
                                        'Process': 'Process',
                                        'Receipted': 'Receipted',
                                        'Cancel': 'Cancel',
                                    }

                                    let div = $(`<div id="wrap-status" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_status" class="form-control"><option value="" selected>Pilih Status...</option></select>`)
                                        .appendTo($('#wrap-status'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                    for (var key in statusList) {
                                        $('#select_status').append(`<option value="${key}">${statusList[key]}</option>`);
                                    }

                                    div.append(select)
                                    $("#select_status").select2({theme: "bootstrap"})
                                }

                                if (name == 'batch_name'){
                                    let batchLists = {!! $listBatch->toJson() !!}

                                    let div = $(`<div id="wrap-batch" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_batch" class="form-control"><option value="" selected>Pilih Batch...</option></select>`)
                                        .appendTo($('#wrap-batch'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                    for (var key in batchLists) {
                                        $('#select_batch').append(`<option value="${key}">${batchLists[key]}</option>`);
                                    }

                                    div.append(select)
                                    $("#select_batch").select2({theme: "bootstrap"})
                                }
                            }
                        });
                },
            })

            // Add an event listener to the search input field
            $('#sell-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }

        $('#partnerId').select2({
            theme: "bootstrap",
            dropdownParent: $('#modalTambahSO'),
            ajax: {
                url: "{{ route('sell.get-first-partner') }}",
                maximumSelectionLength: 1,
                data: function (params) {
                    return {
                        search: params.term,
                        forDue: true,
                    }
                },
                processResults: function (data) {
                    return data
                }
            },
        })

        $('#partnerIdPrint').select2({
            theme: "bootstrap",
            dropdownParent: $('#modalSisaStock'),
            ajax: {
                url: "{{ route('sell.get-first-partner') }}",
                maximumSelectionLength: 1,
                data: function (params) {
                    return {
                        search: params.term,
                        forDue: false,
                    }
                },
                processResults: function (data) {
                    return data
                }
            },
        })

        $('#partnerIdRetur').select2({
            theme: "bootstrap",
            dropdownParent: $('#modalCurrentStock'),
            ajax: {
                url: "{{ route('sell.get-first-partner') }}",
                maximumSelectionLength: 1,
                data: function (params) {
                    return {
                        search: params.term,
                        forDue: false,
                    }
                },
                processResults: function (data) {
                    return data
                }
            },
        })

        $('#pilihMitraAsal').on('click', function(e) {
            e.preventDefault();
            
            $('#modalPilihMitraAsal').modal({
                show: true
            })
        })

        $("#partnerSourceId").select2({
            theme: "bootstrap",
            dropdownParent: $('#modalPilihMitraAsal'),
            ajax: {
                url: "{{ route('mitra.getMitra') }}",
                maximumSelectionLength: 1,
                data: function (params) {
                    return {
                        exclude: [1],
                        forDataTable: false,
                        search: params.term
                    }
                },
                processResults: function (data) {
                    return data
                }
            },
        })
    </script>
@endsection
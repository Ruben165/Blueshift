@extends('layouts.app')

@section('title', 'List Stok Item')

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
        <h1>List Stock Item</h1>
        <div class="right_header">
            @if($type == 'hq')
            <a href="{{ route('item.stock.export') }}" class="btn btn-success mb-3">
                <span class="fas fa-file-excel mr-2"></span>Export Stock Gudang Pusat
            </a>
            <form action="{{ route('item.stock.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group ml-2">
                    <label for="file" class="btn btn-primary mb-3">
                        <span class="fas fa-upload mr-2"></span>Import Stock Gudang Pusat
                    </label>
                    <input type="file" name="file" id="file" onchange="this.form.submit()" hidden>
                </div>
            </form>
            @elseif($type == 'partner')
            <button class="btn btn-success mb-3 mr-2" id="exportStockMitra">
                <span class="fas fa-file-excel mr-2"></span>Export Stock Mitra
            </button>
            @endif
            @if(!$type)
            <a href="{{ route('item.stock.print-barcode') }}" class="btn btn-primary mb-3 ml-2">
                <span class="fas fa-print mr-2"></span>Print Barcode
            </a>
            @endif
        </div>
    </div>
@endsection

@section('content')
    @include('item.stock.__table')
    <div class="modal hide fade" tabindex="-1" id="modalPrintBarcode">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Print Barcode</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('item.stock.print-barcode-each') }}" method="post" target="_blank">
                @csrf
                    <input type="hidden" name="id" id="partnerItemId">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="quantity">Masukkan Kuantitas:</label>
                            <input type="number" min="0" name="quantity" id="quantity" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalExportStockMitra">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Stock Mitra</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('item.stock.export-mitra') }}" method="post" target="_blank">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="partnerId">Pilih Mitra:</label>
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

        .form-group{
            margin-bottom: 0;
        }

        .form-group label{
            font-weight: normal !important;
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

        #partnerIdContainer .select2-container{
            width: 100% !important;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">
        $(document).ready(function() {
            //Inisialisasi Database
            initSupplierDatatable();

            $(document).on('click', '.Edit', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Barcode', function(){
                $('#partnerItemId').val($(this).attr('buttonId'))

                $('#modalPrintBarcode').modal({
                    show: true
                })
            })

            $(document).on('click', '.Hapus', function(){
                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Stock item di mitra ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    reverseButtons: true
                    }).then((result) => {
                    if (result.isConfirmed) {
                        let routeUrl = $(this).attr("href")
                        window.location.href = routeUrl
                    }
                });
            })

            $(document).on('click', '#exportStockMitra', function() {
                $('#modalExportStockMitra').modal({
                    show: true
                })
            })
        });

        function initSupplierDatatable(){
            let table = $('#stock-list').DataTable({
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route, ['type' => $type, 'stock' => 'available']) }}",
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
                        "data": 'actions',
                        "targets": 16,
                        "render": function(data, type, row, meta) {
                            if (data !== '') {
                                let actionContent = `<div style='display: flex; gap:0.5em;'>`;

                                data.map((button, idx) => {
                                    actionContent += 
                                    `<button href="${button.route}" buttonId="${button.attr_id}" class="btn btn-${button.btnStyle} btn-sm ${button.label}">
                                            <div style="display:flex; align-items:center;">
                                                <span class="${button.icon}"></span>
                                                <span style="margin-left: 0.25em">${button.label}</span>
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
                        "targets": [0, 16]
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
                            columnArr = ['partner_name', 'stock_qty']

                            var columns = table.settings().init().columnDefs;
                            var _changeInterval = null;
                            let name = columns[i].name

                            if (columnArr.includes(name)) {
                                var column = this;

                                if (name == 'partner_name') {
                                    let div = $(`<div id="wrap-select" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Mitra...</option></select>`)
                                        .appendTo($('#wrap-select'))
                                        .on('change', function() {
                                            var val = $(this).val();

                                            column.search(val, false, false).draw();
                                        });

                                    div.append(select)

                                    if('{{ $type }}' != 'sales'){
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

                                if (name == 'stock_qty') {
                                    let div = $(`<div id="wrap-stock" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`
                                        <select id="select_stock" class="form-control">
                                            <option value="available" selected>Available</option>
                                            <option value="all">All</option>
                                        </select>`)
                                        .appendTo($('#wrap-stock'))
                                        .on('change', function() {
                                            var val = $(this).val();

                                            if(val == 'available'){
                                                table.ajax.url("{{ route($route, ['type' => $type, 'stock' => 'available']) }}").load()
                                            }
                                            else{
                                                table.ajax.url("{{ route($route, ['type' => $type, 'stock' => 'all']) }}").load()
                                            }
                                        });

                                    div.append(select)

                                    $("#select_stock").select2({
                                        theme: "bootstrap"
                                    })
                                }
                            }
                        });
                },
            })

            // Add an event listener to the search input field
            $('#stock-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }

        $('#partnerId').select2({
            theme: "bootstrap",
            dropdownParent: $('#modalExportStockMitra'),
            ajax: {
                url: "{{ route('item.stock.get-partner') }}",
                maximumSelectionLength: 1,
                data: function (params) {
                    return {
                        search: params.term,
                    }
                },
                processResults: function (data) {
                    return data
                }
            },
        })
    </script>
@endsection
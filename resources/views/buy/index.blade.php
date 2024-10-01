@extends('layouts.app')

@section('title', 'List Pembelian')

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
        <h1>List Pembelian</h1>
        
        <div>
            <button class="btn btn-success mb-3 mr-2" id="exportDetail">
                <span class="fas fa-print mr-2"></span>Export Detail List Pembelian
            </button>
            <a href="{{ route('buy.create') }}" class="btn btn-primary mb-3">
                <span class="fas fa-plus mr-2"></span>Tambah Order
            </a>
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
                <form action="{{ route('buy.export-list-detail') }}" method="post" target="_blank">
                @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="daterange">Pilih Jenis Tanggal:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <select name="datetype" id="datetype" class="form-control" required>
                                            <option value="SP_date" selected>Tanggal SP</option>
                                            <option value="approve_date">Tanggal Approve</option>
                                            <option value="send_date">Tanggal Kirim</option>
                                            <option value="receive_date">Tanggal Terima</option>
                                        </select>
                                    </div><br>
                                    <label for="daterange">Pilih Rentang Tanggal:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
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

@section('content')
    @include('buy.__table')
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

        #buy-list.table.table-bordered.table-hover.dataTable{
            margin: 0;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">

        $(document).ready(function() {
            //Inisialisasi Database
            initBuyDatatable();

            $(document).on('click', '.Detail', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Edit', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Cancel', function(){
                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Semua data pembelian yang ada di data ini akan dibatalkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ganti Status',
                    reverseButtons: true
                    }).then((result) => {
                    if (result.isConfirmed) {
                        let routeUrl = $(this).attr("href")
                        window.location.href = routeUrl
                    }
                });
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
        });

        function initBuyDatatable(){
            let table = $('#buy-list').DataTable({
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "SP_no",
                        "name": "SP_no",
                        "targets": 1
                    },
                    {
                        "data": "supplier_id",
                        "name": "supplier_id",
                        "targets": 2
                    },
                    {
                        "data": "status_id",
                        "name": "status_id",
                        "targets": 3
                    },
                    {
                        "data": "SP_date",
                        "name": "SP_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.SP_date.display
                        },
                        "targets": 4
                    },
                    {
                        "data": "approve_date",
                        "name": "approve_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.approve_date.display
                        },
                        "targets": 5
                    },
                    {
                        "data": "send_date",
                        "name": "send_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.send_date.display
                        },
                        "targets": 6
                    },
                    {
                        "data": "receive_date",
                        "name": "receive_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.receive_date.display
                        },
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
                                    `<button href="${button.route}" class="btn btn-${button.btnStyle} btn-sm ${button.label}">
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
                            columnArr = ['supplier_id', 'status_id']

                            var columns = table.settings().init().columnDefs;
                            var _changeInterval = null;
                            let name = columns[i].name

                            if (columnArr.includes(name)) {
                                var column = this;

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
                                else if(name == 'supplier_id'){
                                    let supplierList = {!! $supplierList !!}
                                    console.log(supplierList)

                                    let div = $(`<div id="wrap-supplier" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_supplier" class="form-control"><option value="" selected>Pilih Supplier</option></select>`)
                                        .appendTo($('#wrap-supplier'))
                                        .on('change', function() {
                                            var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                            column.search(val, false, false).draw();
                                        });


                                    for (var key in supplierList) {
                                        if (supplierList.hasOwnProperty(key)) {
                                            $('#select_supplier').append(`<option value="${supplierList[key]}">${supplierList[key]}</option>`);
                                        }
                                    }

                                    div.append(select)
                                    $("#select_supplier").select2({theme: "bootstrap"})
                                }
                            }
                        });
                },
            })

            // Add an event listener to the search input field
            $('#buy-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }
    </script>
@endsection
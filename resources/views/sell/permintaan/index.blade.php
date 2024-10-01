@extends('layouts.app')

@if($type == 'Reguler')
@section('title', 'List Permintaan Reguler')
@else
@section('title', 'List Permintaan Konsinyasi')
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
        <h1>List Permintaan {{ $type }}</h1>
        <div>
            <button class="btn btn-success mb-3 mr-2" id="exportDetail">
                <span class="fas fa-print mr-2"></span>Export Detail List Permintaan {{ $type }}
            </button>
            <a href="{{ route('sell.permintaan.create', ['type' => $type]) }}" class="btn btn-primary mb-3">
                <span class="fas fa-plus mr-2"></span>Tambah Permintaan
            </a>
        </div>
    </div>
@endsection

@section('content')
    @include('sell.permintaan.__table')
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
                                            <option value="created" selected>Tanggal Permintaan</option>
                                            <option value="delivered">Tanggal Pengiriman</option>
                                        </select>
                                    </div><br>
                                    <label for="daterange">Pilih Rentang Tanggal:</label>
                                    <br>
                                    <div style="width: 100%" id="partnerIdContainer">
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        <input type="hidden" name="isSellOrder" value="false">
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

        $(document).ready(function() {
            //Inisialisasi Database
            initPermintaanKonsinyasiDatatable();

            $(document).on('click', '.Detail', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Edit', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
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
                // swal.fire({
                //     title: 'Apakah anda yakin?',
                //     text: "Semua data permintaan yang ada di data ini akan dibatalkan!",
                //     icon: 'warning',
                //     showCancelButton: true,
                //     confirmButtonColor: '#d33',
                //     cancelButtonColor: '#3085d6',
                //     confirmButtonText: 'Ganti Status',
                //     reverseButtons: true
                //     }).then((result) => {
                //     if (result.isConfirmed) {
                //         let routeUrl = $(this).attr("href")
                //         window.location.href = routeUrl
                //     }
                // });
                $('#cancelForm').attr('action', $(this).attr("href"))

                $('#modalPICCancel').modal({
                    show: true
                })
            })
        });

        function initPermintaanKonsinyasiDatatable(){
            let table = $('#permintaan-konsinyasi-list').DataTable({
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "order": [[0, 'desc'], [5, 'desc'], [4, 'desc']],
                "ajax": "{{ route($route, ['type' => $type]) }}",
                "columnDefs": [
                    {
                        "data": "id_request",
                        "name": "id_request",
                        "targets": 0
                    },
                    {
                        "data": "send_id",
                        "name": "send_id",
                        "targets": 1
                    },
                    {
                        "data": "nama_klinik",
                        "name": "nama_klinik",
                        "targets": 2
                    },
                    {
                        "data": "nama_sales",
                        "name": "nama_sales",
                        "targets": 3,
                    },
                    {
                        "data": "request_date",
                        "name": "request_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.request_date.display
                        },
                        "targets": 4,
                    },
                    {
                        "data": "deliver_date",
                        "name": "deliver_date.timestamp",
                        "render": function(data, type, row, meta) {
                            return row.deliver_date.display
                        },
                        "targets": 5,
                    },
                    {
                        "data": "status_name",
                        "name": "status_name",
                        "targets": 6,
                    },
                    {
                        "data": 'actions',
                        "targets": 7,
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
                        "targets": [7]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false,
                    return: true
                },
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function(i) {
                            columnArr = ['nama_klinik', 'status_name', 'tanggal_buat.timestamp', 'tanggal_proses.timestamp']

                            var columns = table.settings().init().columnDefs;
                            var _changeInterval = null;
                            let name = columns[i].name

                            if (columnArr.includes(name)) {
                                var column = this;

                                if (name == 'nama_klinik') {
                                    let div = $(`<div id="wrap-partner" class="form-group"></div>`).appendTo($(column.footer()).empty())

                                    var select = $(`<select id="select_partner" class="form-control"><option value="" selected>Pilih Klinik...</option></select>`)
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
                                else if (name == 'status_name') {
                                    let statusList = {
                                        'Baru': 'Baru',
                                        'Process': 'Process',
                                        'Complete': 'Complete',
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
                                else{
                                    var input = $('<input class="form-control" type="date">')
                                        .appendTo($(column.footer()).empty())
                                        .on('change', function() {
                                            var val = $(this).val();

                                            column.search(val, false, false).draw();
                                        });
                                }
                            }
                        });
                },
            })

            // Add an event listener to the search input field
            $('#permintaan-konsinyasi-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }
    </script>
@endsection
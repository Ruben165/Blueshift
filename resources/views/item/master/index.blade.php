@extends('layouts.app')

@section('title', 'List Master Item')

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
    @if($errors->first('file'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
            {{ $errors->first('file') }}
        </div>
    @endif
    <div class="content_header">
        <h1>List Master Item</h1>
        <div class="right_header">
            <a href="{{ route('item.sku.export') }}" class="btn btn-success mr-2">
                <span class="fas fa-file-excel mr-2"></span>Export Item
            </a>
            <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file" class="btn btn-primary" style="margin-bottom: 0 !important;">
                        <span class="fas fa-plus mr-2"></span>Import Data
                    </label>
                    <input type="file" name="file" id="file" onchange="this.form.submit()" hidden>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('content')
    @include('item.master.__table')
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

        .form-group{
            margin-bottom: 0;
        }

        .form-group label{
            font-weight: normal !important;
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

            $(document).on('click', '.Hapus', function(){
                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Semua data supplier beserta item yang supplier ini sediakan akan dihapus secara permanen!",
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
        });

        function initSupplierDatatable(){
            let table = $('#master-list').DataTable({
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
                        "data": "sku",
                        "name": "sku",
                        "targets": 1
                    },
                    {
                        "data": "name",
                        "name": "name",
                        "targets": 2
                    },
                    {
                        "data": "type_id",
                        "name": "type_id",
                        "targets": 3
                    },
                    {
                        "data": "unit",
                        "name": "unit",
                        "targets": 4
                    },
                    {
                        "data": "manufacturer",
                        "name": "manufacturer",
                        "targets": 5
                    },
                    {
                        "data": "supplier_id",
                        "name": "supplier_id",
                        "targets": 6
                    },
                    {
                        "data": "price",
                        "name": "price",
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
                        "targets": [0, 8]
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
            })

            // Add an event listener to the search input field
            $('#master-list_filter input').off().on('keyup', function (e) {
                // Check if the user pressed Enter (key code 13)
                if (e.keyCode === 13) {
                    // Perform the search
                    table.search(this.value).draw();
                }
            });
        }
    </script>
@endsection
@extends('layouts.app')

@section('title', 'List Supplier')

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
        <h1>List Supplier</h1>
        <a href="{{ route('supplier.create') }}" class="btn btn-primary mb-3">
            <span class="fas fa-plus mr-2"></span>Tambah Supplier
        </a>
    </div>
@endsection

@section('content')
    @include('supplier.__table')
@endsection

@section('extra-css')
    <style scoped>
        .content_header{
            display: flex;
            justify-content: space-between;
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
            let table = $('#suppliers-list').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "supplier_code",
                        "name": "supplier_code",
                        "targets": 1
                    },
                    {
                        "data": "name",
                        "name": "name",
                        "targets": 2
                    },
                    {
                        "data": "email",
                        "name": "email",
                        "targets": 3
                    },
                    {
                        "data": "phone",
                        "name": "phone",
                        "targets": 4
                    },
                    {
                        "data": "address",
                        "name": "address",
                        "targets": 5
                    },
                    {
                        "data": "npwp",
                        "name": "npwp",
                        "targets": 6
                    },
                    {
                        "data": 'actions',
                        "targets": 7,
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
                        "targets": [0, 7]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td').first().text((table.page() * table.page.len()) + dataIndex + 1);
                },
            })
        }
    </script>
@endsection
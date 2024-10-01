@extends('layouts.app')

@section('title', 'List Mitra')

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
        <h1>List Mitra</h1>
        <div>
            {{-- <a href="{{ route('mitra.export-all') }}" class="btn btn-success mb-3">
                <span class="fas fa-print mr-2"></span>Export List Mitra
            </a> --}}
            <a href="{{ route('mitra.create') }}" class="btn btn-primary mb-3">
                <span class="fas fa-plus mr-2"></span>Tambah Mitra
            </a>
        </div>
    </div>
@endsection

@section('content')
    @include('partner.__table')
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
            initPartnerDatatable();

            $(document).on('click', '.Detail', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Edit', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Hapus', function(){
                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Semua data mitra beserta seluruh data pembelian/penjualan yang mitra ini lakukan akan dihapus secara permanen!",
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

        function initPartnerDatatable(){
            let table = $('#partners-list').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route) }}",
                "columnDefs": [
                    {
                        "data": "name",
                        "name": "name",
                        "targets": 0
                    },
                    {
                        "data": "clinic_id",
                        "name": "clinic_id",
                        "targets": 1
                    },
                    {
                        "data": "email",
                        "name": "email",
                        "targets": 2
                    },
                    {
                        "data": "phone",
                        "name": "phone",
                        "targets": 3
                    },
                    {
                        "data": "allow_consign",
                        "name": "allow_consign",
                        "targets": 4,
                        "render": function(data, type, row, meta) {
                            return data == 1 ? 'Konsinyasi' : 'Reguler'
                        }
                    },
                    {
                        "data": "groups",
                        "name": "groups",
                        "targets": 5
                    },
                    {
                        "data": "zones",
                        "name": "zones",
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
                        "targets": [7]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false
                },
            })
        }
    </script>
@endsection
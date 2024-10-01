@extends('layouts.app')

@section('title', 'Edit Wilayah Mitra')

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
        <h1>Edit Wilayah Mitra {{ $wilayah->name }}</h1>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3">
            @include('master.zone.__formAddPartner')
            @include('master.zone.__formEdit')
        </div>
        <div class="col-md-9">
            @include('master.zone.__tablePartner')
        </div>
    </div>
@endsection

@section('extra-css')
    <style scoped>
        .select2-container .select2-selection--multiple .select2-selection__rendered {
            height: auto;
            max-height: none;
            display: block;
        }

        .select2-selection__choice__display{
            color: black;
            padding: 0 0.5em !important;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">
        $(document).ready(function() {
            //Inisialisasi Database
            initPartnerDatatable();

            $(document).ready(function() {
                $('#clinic_id').select2({
                    ajax: {
                        url: "{{ route('wilayah.get-all-partners') }}",
                        data: function (params) {
                            return {
                                search: params.term
                            }
                        },
                        processResults: function (data) {
                            return data
                        }
                    }
                });
            });

            $(document).on('click', '.Hapus', async function(){
                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Mitra ini akan dihapus dari wilayah {{ $wilayah->name }}!",
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
                "ajax": "{{ route($route, ['wilayah' => $wilayah->id]) }}",
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
                        "data": 'actions',
                        "targets": 2,
                        "render": function(data, type, row, meta) {
                            if (data !== '') {
                                let actionContent = `<div style='display: flex; gap:0.5em;'>`;

                                data.map((button, idx) => {
                                    actionContent += 
                                    `<button href="${button.route}" class="btn btn-${button.btnStyle} btn-sm ${button.class}" id="${button.attr_id}">
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
                        "targets": [2]
                    }
                ],
                search: {
                    smart: false,
                    "caseInsensitive": false
                }
            })
        }
    </script>
@endsection
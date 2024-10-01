@extends('layouts.app')

@section('title', 'Edit Rak')

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
        <h1>Edit Rak {{ $rak->name }}</h1>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3">
            @include('master.shelf.__formEdit')
        </div>
        <div class="col-md-9">
            @include('master.shelf.__tableStock')
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
            initDatatable();
        });

        function initDatatable(){
            let table = $('#stock-list').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route, ['rak' => $rak->id]) }}",
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
                        "data": "item_name",
                        "name": "item_name",
                        "targets": 2
                    },
                    {
                        "data": "qty",
                        "name": "qty",
                        "targets": 3
                    },
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": [1, 3]
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
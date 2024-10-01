@extends('layouts.app')

@section('title', 'List Batch Mitra')

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
        <h1>List Batch Mitra</h1>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            @include('master.group.__table')
        </div>
        <div class="col-sm-4">
            @include('master.group.new')
        </div>
    </div>
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
            initGroupDatatable();

            $(document).on('click', '.Edit', function(){
                let routeUrl = $(this).attr("href")
                window.location.href = routeUrl
            })

            $(document).on('click', '.Hapus', async function(){
                let dataPartner = await getPartnerFromGroupId($(this).attr("id"));

                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Batch mitra akan dihapus, seluruh partner yang berhubungan dengan batch ini akan kehilangan batch!",
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

        function initGroupDatatable(){
            let table = $('#groups-list').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route($route) }}",
                "columnDefs": [
                    {
                        "data": null,
                        "targets": 0
                    },
                    {
                        "data": "name",
                        "name": "name",
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
                        "targets": [0, 2]
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

        function getPartnerFromGroupId(idGroup){
            $.ajax({
                url: "/admin/master/batch-mitra/"+idGroup+"/get-partners",
                method: 'GET',
                success: function(response){
                    return response
                },
                error: function(xhr, status, error){
                    console.log(xhr.responseText)
                }
            });
        }
    </script>
@endsection
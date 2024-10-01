@extends('layouts.app')

@section('title', 'Stock Opname Konsinyasi')

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
        <h1>Stock Opname {{ $sell->destinationPartner->clinic_id  }} - {{ $sell->destinationPartner->name }}</h1>
        <form action="{{ route('sell.so.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
            <input type="hidden" name="partnerId" value="{{ $sell->destination_partner_id }}">
            <div class="form-group">
                <label for="file" class="btn btn-success mb-3">
                    <span class="fas fa-plus mr-2"></span>Import Hasil SO
                </label>
                <input type="file" name="file" id="file" onchange="this.form.submit()" hidden>
            </div>
        </form>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <table>
                <tr>
                    {{-- Berhenti Di sini --}}
                    <th>Klinik Sumber</th>
                    <td>: {{ $synapsa->clinic_id . ' - ' . $synapsa->name }}</td>
                </tr>
                <tr>
                    <th>Klinik Destinasi</th>
                    <td>: {{ $sell->destinationPartner->clinic_id  }} - {{ $sell->destinationPartner->name }}</td>
                </tr>
            </table>
        </div>
    </div>
    <form method="POST" action="{{ route('sell.store-so-autofill', ['partner' => $sell->destinationPartner->id, 'sell' => $sell->id]) }}" id='theForm'>
    @csrf
        <input type="hidden" name="type" value="SO">
        <div class="row">
            <div class="col-md-12">
                @include('sell.__item-so')
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="description">Description SO</label>
                            <textarea type="number" name="description" id="description" placeholder="Masukkan description konsinyasi..." class="form-control"></textarea>
                        </div>
                    </div>                    
                </div>
            </div>
            <div class="col-md-12">
                <button id="terimaPesanan" class="btn btn-primary mr-3 mb-5">
                    Submit
                </button>
            </div>
        </div>
    </form>
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
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da !important;
            background-color: white;
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
        let inputedItems = [];

        $(document).ready(function() {
            $('#terimaPesanan').on('click', function(e){
                e.preventDefault();

                let SOSoldQuantity = 0
                let isAnyMinus = false

                $('input[name="soSoldQuantity[]"]').each(function() {
                    let quantitySO = parseInt($(this).val());
                    SOSoldQuantity += quantitySO

                    if(quantitySO < 0){
                        isAnyMinus = true
                    }
                })

                if(isAnyMinus == true){
                    swal.fire(
                        'Warning!',
                        'Terdapat item dengan kuantitas update melebihi kuantitas awal!',
                        'error'
                    )
                    return;
                }

                if(SOSoldQuantity > 0){
                    let soQuantities = $('input[name="soSoldQuantity[]"]')
                }

                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Stock di klinik sumber akan berkurang secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#218838',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Submit',
                    reverseButtons: true
                    }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).hide();
                        $('#theForm').submit()
                    }
                });
            })
        });
    </script>
@endsection
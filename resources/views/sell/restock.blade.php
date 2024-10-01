@extends('layouts.app')

@section('title', 'Restock Konsinyasi')

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
        <h1>Restock {{ $partnerItems[0]->partner->clinic_id }} - {{ $partnerItems[0]->partner->name }}</h1>
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
                    <td>: {{ $partnerItems[0]->partner->clinic_id . ' - ' . $partnerItems[0]->partner->name }}</td>
                </tr>
            </table>
        </div>
    </div>
    <form method="POST" action="{{ route('sell.store-so-autofill', ['partner' => $partnerItems[0]->partner->id, 'sell' => $idSellOrder]) }}" id='theForm'>
    @csrf
        <input type="hidden" name="type" value="Restock">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="noKonsinyasi">Nomor Invoice</label>
                            <input type="number" name="noKonsinyasi" id="noKonsinyasi" placeholder="Masukkan nomor invoice konsinyasi..." class="form-control">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="descriptionKonsinyasi">Description Invoice</label>
                            <textarea type="number" name="descriptionKonsinyasi" id="descriptionKonsinyasi" placeholder="Masukkan description konsinyasi..." class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                @include('sell.__item-restock')
            </div>
            <div class="col-md-12">
                <button id="terimaPesanan" class="btn btn-primary mr-3">
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

                let refillQuantity = 0;


                $('input[name="refillQuantity[]"]').each(function() {
                    let quantityRefill = parseInt($(this).val());
                    refillQuantity += quantityRefill
                })


                if(refillQuantity > 0 && $('#noKonsinyasi').val() == ''){
                    swal.fire(
                        'Warning!',
                        'Tolong masukkan nomor invoice konsinyasi!',
                        'error'
                    )
                    return;
                }

                swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Stock di klinik synapsa akan berkurang secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#218838',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Submit',
                    reverseButtons: true
                    }).then((result) => {
                    if (result.isConfirmed) {
                        $('#theForm').submit()
                    }
                });
            })
        });
    </script>
@endsection
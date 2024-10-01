@extends('layouts.app')

@if($sell->sellOrderType->name == 'Penjualan Reguler')
@section('title', 'Detail Pengiriman Reguler')
@elseif($sell->sellOrderType->name == 'Konsinyasi')
@section('title', 'Detail Konsinyasi')
@elseif($sell->sellOrderType->name == 'Transfer')
@section('title', 'Detail Transfer')
@elseif($sell->sellOrderType->name == 'Retur')
@section('title', 'Detail Retur')
@else
@section('title', 'Detail Stock Opname')
@endif

@section('plugins.BsCustomFileInput', true)

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
        <h1>List Item {{ $sell->sellOrderType->name == 'Penjualan Reguler' ? 'Pengiriman Reguler' : $sell->sellOrderType->name }}: {{ $sell->document_number }}</h1>
        <div class="right_header">
            <a href="{{ route('sell.export-excel', ['sell' => $sell->id ]) }}" class="btn btn-success mb-3">
                <span class="fas fa-file-excel mr-2"></span>Excel
            </a>
            @if($sell->sell_order_type_id != 5)
            <a href="{{ route('sell.export-pdf', ['sell' => $sell->id ]) }}" target="_blank" class="btn btn-danger mb-3 ml-2">
                <span class="fas fa-file-pdf mr-2"></span>{{ $sell->sellOrderType->name == 'Retur' ? 'Dokumen Konfirmasi' : 'Surat Jalan' }}
            </a>
            @endif
            @if(in_array($sell->sell_order_type_id, [1, 5]) && $sell->status_id == 2)
                @if($sell->path == null)
                <a href="#" class="btn btn-danger mb-3 ml-2" id="exportInvoice">
                    <span class="fas fa-file-pdf mr-2"></span>Invoice
                </a>
                @else
                <a href="{{ asset('storage/' . $sell->path) }}" target="_blank" class="btn btn-danger mb-3 ml-2">
                    <span class="fas fa-file-pdf mr-2"></span>Invoice
                </a>
                @if($sell->buktiPembayaran != null)
                <a href="{{ asset('storage/' . $sell->buktiPembayaran) }}" target="_blank" class="btn btn-primary mb-3 ml-2">
                    <span class="fas fa-receipt mr-2"></span>Bukti Bayar
                </a>
                @endif
                <div class="ml-2">
                    <form action="{{ route('sell.upload-bukti-bayar', ['sell' => $sell->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                        <label for="file" class="btn btn-primary mb-3">
                            <span class="fas fa-plus"></span>
                            @if($sell->buktiPembayaran == null)
                            <span class="ml-2">
                                Bukti Bayar
                            </span>
                            @endif
                        </label>
                        <input type="file" name="file" id="file"  onchange="this.form.submit()" hidden>
                    </form>
                </div>
                @endif
            @endif
        </div>
    </div>
    <div class="modal hide fade" tabindex="-1" id="modalAddInfoInvoice">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Export Invoice</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="{{ route('sell.export-pdf-reguler', ['sell' => $sell->id]) }}" method="post">
            @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="noTagihan">Nomor Tagihan</label>
                        <input type="text" class="form-control" name="noTagihan" placeholder="Masukkan Nomor Tagihan..." required>
                    </div>
                    <div class="form-group">
                        <label for="customerId">Customer ID</label>
                        <input type="text" class="form-control" name="customerId" placeholder="Masukkan ID Customer..." required>
                    </div>
                    <div class="form-group">
                        <label for="namaSales">Nama Sales</label>
                        <input type="text" class="form-control" name="namaSales" placeholder="Masukkan Nama Sales..." required>
                    </div>
                    <div class="form-group">
                        <label for="expiredDate">Jatuh Tempo</label>
                        <input type="date" class="form-control" name="expiredDate" placeholder="Masukkan Jatuh Tempo..." required>
                    </div>
                    <div class="form-group">
                        <label for="note">Catatan</label>
                        <textarea class="form-control" name="note" placeholder="Masukkan Catatan..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="payment">Pembayaran Dapat Ditransfer ke rekening:</label>
                        <textarea class="form-control" name="payment" placeholder="Masukkan nama bank, nomor akun, dan nama secara lengkap..." required></textarea>
                    </div>
                    <small style="color: red;">*Setelah diexport, invoice akan tersimpan secara otomatis di database secara permanen. Harap isi dengan benar!</small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" formtarget="_blank">Export</button>
                </div>
            </form>
          </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <table>
                <tr>
                    <th>Klinik Sumber</th>
                    <td>: {{ $sell->sourcePartner->clinic_id . ' - ' . $sell->sourcePartner->name }}</td>
                </tr>
                @if($sell->sell_order_type_id != 4)
                <tr>
                    <th>Klinik Destinasi</th>
                    <td>: {{ $sell->destinationPartner->clinic_id . ' - ' . $sell->destinationPartner->name }}</td>
                </tr>
                @endif
                @if($sell->status_id == 2 && $sell->sell_order_type_id == 2)
                <tr>
                    <th>Kode Konsinyasi</th>
                    <td>: {{ $sell->status_kode }}</td>
                </tr>
                <tr>
                    <th>Due Konsinyasi FIRST</th>
                    <td>: {{ date('d-m-Y', strtotime($sell->due_at)) }}</td>
                </tr>
                @endif
                @if($sell->sell_order_type_id == 1 || $sell->sell_order_type_id == 2)
                <tr>
                    <th>ID Pengiriman</th>
                    <td>: {{ $sell->document_number }}</td>
                </tr>
                <tr>
                    <th>Tanggal Pengiriman</th>
                    <td>: {{ $sell->delivered_at }}</td>
                </tr>
                <tr>
                    <th>Tanggal Permintaan</th>
                    <td>: {{ $sell->created_at }}</td>
                </tr>
                @elseif($sell->sell_order_type_id == 4)
                <tr>
                    <th>Tanggal Retur</th>
                    <td>: {{ $sell->returned_at ?? '-' }}</td>
                </tr>
                <tr>
                    <th>PIC Retur</th>
                    <td>: {{ $sell->pic_retur ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @if($sell->sell_order_type_id == 4)
                @include('sell.__item-retur-table')
            @else
                @include('sell.__item-table')
            @endif
        </div>
        <div class="col-md-12">
            @if($sell->status_id == 1)
            <form method="POST" action="{{ route('sell.terima-pesanan', ['sell' => $sell->id]) }}" id="theForm" enctype="multipart/form-data">
            @csrf
                @if($sell->sell_order_type_id == 3)
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="description">Description Transfer</label>
                            <textarea type="number" name="descriptionTransfer" id="descriptionTransfer" disabled class="form-control">{{ $sell->description ?? '-' }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Description Konsinyasi</label>
                            <textarea type="number" name="description" id="description" placeholder="Masukkan description konsinyasi..." class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                @elseif(in_array($sell->sell_order_type_id, [1, 2, 4]))
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea type="number" name="description" id="description" disabled class="form-control">{{ $sell->description ?? '-' }}</textarea>
                        </div>
                    </div>
                </div>
                    @if($sell->sell_order_type_id != 4)
                    <div class="row">
                        <div class="col-md-3">
                            <x-adminlte-input-file name="surat_jalan_result" id="surat_jalan_result" placeholder="Choose a file..." label="Upload Hasil Surat Jalan" required/>
                        </div>
                    </div>
                    @endif
                @endif
                @if($sell->path_surat_jalan != null && $sell->sellOrderType->name == 'Retur')
                    <div class="row">
                        <a href="{{ asset('storage/' . $sell->path_surat_jalan) }}" target="_blank" class="btn btn-info mb-3 ml-2">
                            <span class="fas fa-file-pdf mr-2"></span>Hasil Bukti Retur
                        </a>
                    </div>
                @elseif($sell->sellOrderType->name == 'Retur')
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-danger fw-bolder">Hasil bukti retur belum diupload, harap upload terlebih dahulu di menu edit!</p>
                        </div>
                    </div>
                @endif
                <button id="terimaPesanan" class="btn btn-primary mb-3">
                    <span class="fas fa-check mr-2"></span>Selesaikan Pesanan
                </button>
            </form>
            @else
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea type="number" name="description" id="description" disabled class="form-control">{{ $sell->description ?? '-' }}</textarea>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @if( $sell->status_id == 3)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <label for="pic_cancel">Nama PIC Cancel</label>
                <input type="text" name="pic_cancel" id="pic_cancel" class="form-control" disabled value="{{ $sell->pic_cancel }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="alasan_cancel">Alasan Cancel</label>
                <textarea name="alasan_cancel" id="alasan_cancel" class="form-control" disabled>{{ $sell->alasan_cancel }}</textarea>
            </div>
        </div>
    </div>
    @if($sell->path_cancel)
        <div class="row">
            <div class="col-sm-3">
                <a href="{{ asset('storage/' . $sell->path_cancel) }}" target="_blank" class="btn btn-danger mb-3 ml-2">
                    <span class="fas fa-file-pdf mr-2"></span>Bukti Cancel
                </a>
            </div>
        </div>
    @endif
    @endif
    @if($sell->path_surat_jalan != null && $sell->sellOrderType->name != 'Retur')
    <div class="row">
        <a href="{{ asset('storage/' . $sell->path_surat_jalan) }}" target="_blank" class="btn btn-info mb-3 ml-2">
            <span class="fas fa-file-pdf mr-2"></span>Hasil Surat Jalan
        </a>
    </div>
    @elseif($sell->path_surat_jalan != null && $sell->sellOrderType->name == 'Retur' && $sell->status_id == 2)
        <div class="row">
            <a href="{{ asset('storage/' . $sell->path_surat_jalan) }}" target="_blank" class="btn btn-info mb-3 ml-2">
                <span class="fas fa-file-pdf mr-2"></span>Hasil Bukti Retur
            </a>
        </div>
    @endif
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

                let sellOrder = {!! $sell->toJson() !!}

                if($('#surat_jalan_result').get(0) != undefined){
                    if($('#surat_jalan_result').get(0).files.length == 0){
                        let msg = 'hasil surat jalan!'

                        if(sellOrder.sell_order_type_id == 4){
                            msg = 'hasil bukti retur!'
                        }

                        swal.fire(
                            'Warning!',
                            'Tolong upload ' + msg,
                            'error'
                        );
                    }
                    else if([1, 2].includes(sellOrder.sell_order_type_id) && (sellOrder.delivered_at == null)){
                        swal.fire(
                            'Warning!',
                            'Tolong isi tanggal pengiriman di menu edit sebelum melakukan proses selesaikan pesanan!',
                            'error'
                        );
                    }
                    else if(sellOrder.sell_order_type_id == 4 && ([sellOrder.delivered_at, sellOrder.returned_at, sellOrder.pic_retur].includes(null) || [sellOrder.delivered_at, sellOrder.returned_at, sellOrder.pic_retur].includes(''))){
                        swal.fire(
                            'Warning!',
                            'Tolong isi tanggal pengiriman, tanggal retur, dan PIC retur di menu edit sebelum melakukan proses selesaikan pesanan!',
                            'error'
                        );
                    }
                    else{
                        swal.fire({
                            title: 'Apakah anda yakin?',
                            text: "Stock di klinik sumber akan berkurang secara permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#218838',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Selesaikan Pesanan',
                            reverseButtons: true
                            }).then((result) => {
                            if (result.isConfirmed) {
                                $('#theForm').submit()
                            }
                        });
                    }
                }
                else{
                    if(sellOrder.sell_order_type_id == 3 && ([null, ''].includes($('#description').val()))){
                        swal.fire(
                            'Warning!',
                            'Tolong isi description konsinyasi!',
                            'error'
                        );
                    }
                    else if(sellOrder.sell_order_type_id == 4 && sellOrder.path_surat_jalan == null){
                        swal.fire(
                            'Warning!',
                            'Tolong upload hasil bukti retur di menu edit terlebih dahulu!',
                            'error'
                        );
                    }
                    else if(sellOrder.sell_order_type_id == 4 && ([sellOrder.returned_at, sellOrder.pic_retur].includes(null) || [sellOrder.returned_at, sellOrder.pic_retur].includes(''))){
                        swal.fire(
                            'Warning!',
                            'Tolong isi tanggal retur dan PIC retur di menu edit sebelum melakukan proses selesaikan pesanan!',
                            'error'
                        );
                    }
                    else{
                        swal.fire({
                            title: 'Apakah anda yakin?',
                            text: "Stock di klinik sumber akan berkurang secara permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#218838',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Selesaikan Pesanan',
                            reverseButtons: true
                            }).then((result) => {
                            if (result.isConfirmed) {
                                $('#theForm').submit()
                            }
                        });
                    }

                }
            })

            $('#exportInvoice').on('click', function(e) {
                e.preventDefault();
                
                $('#modalAddInfoInvoice').modal({
                    show: true
                })
            })
        });
    </script>
@endsection
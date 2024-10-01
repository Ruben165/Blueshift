@extends('layouts.app')

@section('title', 'Detail Permintaan')

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
        <h1>Detail Permintaan {{ $type }} #{{ $consignmentRequest->id_request }}</h1>
        <div class="right_header">
            <a href="{{ route('sell.permintaan.export-excel', ['consignmentRequest' => $consignmentRequest->id, 'type' => $type]) }}" class="btn btn-success mb-3">
                <span class="fas fa-file-excel mr-2"></span>Export Excel
            </a>
            <a href="{{ route('sell.permintaan.export-pdf', ['consignmentRequest' => $consignmentRequest->id, 'type' => $type]) }}" target="_blank" class="btn btn-danger mb-3 ml-2">
                <span class="fas fa-file-pdf mr-2"></span>Export PDF
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <table>
                <tr>
                    <th>Tanggal Buat</th>
                    <td>: {{ Carbon\Carbon::parse($consignmentRequest->created_at)->format('d-m-Y') }}</td>
                </tr>
                @if($consignmentRequest->status_id == 1 || $consignmentRequest->status_id == 4)
                <tr>
                    <th>Tanggal Proses</th>
                    <td>: {{ Carbon\Carbon::parse($consignmentRequest->processed_at)->format('d-m-Y') }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="namaKlinik">Nama Klinik:</label>
                <input type="text" id="namaKlinik" class="form-control" value="{{ $consignmentRequest->partner->name }}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="namaSales">Nama Sales:</label>
                <input type="text" id="namaSales" class="form-control" value="{{ $consignmentRequest->partner->sales_name }}" disabled>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @include('sell.permintaan.__item-table')
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <label for="request_date">Tanggal Permintaan</label>
                <input type="date" name="request_date" id="request_date" class="form-control" value="{{ $consignmentRequestAdditionalData['request_date'] }}" readonly="true">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label for="deliver_date">Tanggal Pengiriman</label>
                <input type="date" name="deliver_date" id="deliver_date" class="form-control" value="{{ $consignmentRequestAdditionalData['deliver_date'] }}" readonly="true">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <label for="sender_id">ID Pengirim</label>
                <input type="text" name="sender_id" id="sender_id" placeholder="Masukkan ID Pengirim..." class="form-control" value="{{ $consignmentRequestAdditionalData['sender_id'] }}" readonly="true">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label for="sender_pic">PIC Pengirim</label>
                <input type="text" name="sender_pic" id="sender_pic" placeholder="Masukkan PIC Pengirim..." class="form-control" value="{{ $consignmentRequestAdditionalData['sender_pic'] }}" readonly="true">
            </div>
        </div>
    </div>
    @if($consignmentRequest->path_surat_permohonan_klinik != null)
    <div class="row">
        <a href="{{ asset('storage/' . $consignmentRequest->path_surat_permohonan_klinik) }}" target="_blank" class="btn btn-info mb-3 ml-2">
            <span class="fas fa-file-pdf mr-2"></span>Surat Permohonan Klinik
        </a>
    </div>
    @endif
    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea type="text" id="description" class="form-control" disabled>{{ $consignmentRequest->description }}</textarea>
            </div>
        </div>
    </div>
    @if( $consignmentRequest->status_id == 3)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <label for="pic_cancel">Nama PIC Cancel</label>
                <input type="text" name="pic_cancel" id="pic_cancel" class="form-control" disabled value="{{ $consignmentRequest->pic_cancel }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="alasan_cancel">Alasan Cancel</label>
                <textarea name="alasan_cancel" id="alasan_cancel" class="form-control" disabled>{{ $consignmentRequest->alasan_cancel }}</textarea>
            </div>
        </div>
    </div>
    @if($consignmentRequest->path_cancel)
        <div class="row">
            <div class="col-sm-3">
                <a href="{{ asset('storage/' . $consignmentRequest->path_cancel) }}" target="_blank" class="btn btn-danger mb-3 ml-2">
                    <span class="fas fa-file-pdf mr-2"></span>Bukti Cancel
                </a>
            </div>
        </div>
    @endif
    @endif
    @if($consignmentRequest->status_id != 4 && $consignmentRequest->status_id != 3)
    <form method="POST" action="{{ route('sell.permintaan.complete', ['consignmentRequest' => $consignmentRequest->id]) }}" onsubmit="return validateForm()">
    @csrf
        <button type="submit" class="btn btn-primary mb-3">
            <span class="fas fa-check mr-2"></span>Selesaikan Permintaan
        </button>
    </form>
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
        function validateForm(){
            let request_date = $('#request_date').val()
            let deliver_date = $('#deliver_date').val()
            let sender_id = $('#sender_id').val()
            let sender_pic = $('#sender_pic').val()

            if(request_date == '' || deliver_date == '' || sender_id == '' || sender_pic == ''){
                swal.fire(
                    'Warning!',
                    'Seluruh field input harus diisi!',
                    'error'
                );

                return false;
            }

            return true;
        }
    </script>
@endsection
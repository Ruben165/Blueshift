@extends('layouts.app')

@section('title', 'Detail Mitra')

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
        <h1>Detail Mitra {{ $mitra->name }}</h1>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3">
            @include('partner.summary')
            @include('partner.info')
        </div>
        <div class="col-md-9">
            @include('partner.__tab')
        </div>
    </div>
@endsection

@section('extra-css')
    <style scoped>
        #logoLabel{
            display: flex;
            justify-content: center;
        }

        #logoImage{
            max-width: 100%;
            max-height: 10em;
            object-fit: cover;
        }

        .nav-tabs .nav-link{
            background-color: white
        }
        
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">

        $(document).ready(function() {
            let input = $('#foto');
            input.val("{{ $mitra->foto }}");
            input.on('change', function() {
                // Remove the value attribute when a new file is selected
                input.removeAttr('value');
            });
        });
    </script>
@endsection
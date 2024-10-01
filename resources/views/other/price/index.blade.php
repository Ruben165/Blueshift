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
        <h1>Penyesuaian Format Harga Obat Supplier</h1>
    </div>
@endsection

@section('content')
    <form action="{{ route('other.price.store') }}" method="POST" enctype="multipart/form-data" id="theForm">
        @csrf
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="type">Pilih Supplier</label>
                    <select class="form-control" id="type" name="type">
                        <option selected value="BPL">BPL</option>
                        <option value="ACM">ACM</option>
                    </select>
                </div>
                <div class="form-group" id="catalogueDividerContainer">
                    <label for="catalogueDivider">Pembagi untuk Harga Katalog</label>
                    <input type="number" class="form-control" id="catalogueDivider" name="catalogueDivider" placeholder="Masukkan pembagi untuk harga katalog..." required value="0.91">
                    <p class="text-danger mt-2" id="alertCatalogueDivider" style="display:none">
                        Kolom ini wajib diisi dan tidak boleh bernilai 0 (nol)
                    </p>
                </div>
            </div>
        </div>
        <div class="form-group" id="fileContainer">
            <label for="file" class="btn btn-primary mb-3">
                <span class="fas fa-plus mr-2"></span>Import Excel
            </label>
            <input type="file" name="file" id="file" hidden>
        </div>
    </form>
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
            if($('#type') == 'ACM'){
                $('#catalogueDividerContainer').hide();
                $('#fileContainer').show();
                $('#catalogueDivider').removeAttr('required');
            }

            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                event.preventDefault();
                return false;
                }
            });

            $('#type').on("change", function() {
                if($(this).val() == 'BPL'){
                    $('#catalogueDividerContainer').show();
                    $('#catalogueDivider').prop('required', true)
                    $('#catalogueDivider').val(0.91)
                    $('#alertCatalogueDivider').hide()
                }
                else{
                    $('#catalogueDividerContainer').hide();
                    $('#fileContainer').show();
                    $('#catalogueDivider').removeAttr('required');
                }
            })

            $('#catalogueDivider').on("change", function() {
                if($(this).val() == null || $(this).val() == 0){
                    $('#alertCatalogueDivider').show()
                    $('#fileContainer').hide()
                }
                else{
                    $('#alertCatalogueDivider').hide()
                    $('#fileContainer').show()
                }
            })

            $('#file').on("change", function() {
                if($(this).val() != null){
                    $('#theForm').submit();
                    $($(this).val(null));
                }
            })
        })
    </script>
@endsection
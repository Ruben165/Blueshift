@extends('layouts.app')

@section('plugins.Chartjs', true)

@section('title', 'Dashboard')

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
    <h1>
        Dashboard
    </h1>
@endsection

@section('content')
        <input type="hidden" id="suppliersCount" value="{{ $suppliersCount }}">
        <input type="hidden" id="zonesCount" value="{{ $zonesCount }}">
        <input type="hidden" id="partnersCount" value="{{ $partnersCount }}">
        <input type="hidden" id="itemSoldCount" value="{{ $itemSoldCount }}">
        <input type="hidden" id="sellListCount" value="{{ $sellListCount }}">
        <input type="hidden" id="scheduleSOCount" value="{{ $scheduleSOCount }}">
        <div class="row mb-4">
            <div class="col-md-12">
                <h4>Penjualan</h4>
                <canvas id="penjualan-list-chart"></canvas>               
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-12">
                <h4>Pembelian/Penjualan</h4>
                <canvas id="penjualan-pembelian-list-chart"></canvas>               
            </div>
        </div>
        <form action="{{ route('dashboard') }}" id="theForm">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="date" name="filterStart" id="filterStart" class="form-control" value="{{ $filterStart ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="date" name="filterEnd" id="filterEnd" class="form-control" value="{{ $filterEnd ?? '' }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
        <div class="row mb-4">
            <div class="col-md-4">
                <h4>Asset Gudang</h4>
                <canvas id="asset-gudang-chart"></canvas>           
            </div>
        </div>
        {{-- <div class="row mb-4"> 
            <div class="col-md-4">
                <h4>List Permintaan</h4>
                <div>
                    <table class="table table-bordered table-hover" id="list-permintaan">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis</th>
                                <th>Jumlah Permintaan</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>List Pengiriman</h4>
                <div>
                    <table class="table table-bordered table-hover" id="list-pengiriman">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis</th>
                                <th>Jumlah Pengiriman</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>Penjualan Klinik</h4>
                <select id="penjualan-klinik">
                    <option value="ALL" selected>ALL</option>
                    <option value="1">REGULER</option>
                    <option value="5">KONSINYASI</option>
                </select>
                <div>
                    <table class="table table-bordered table-hover" id="penjualan-klinik-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Klinik</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <h4>List Permintaan Klinik</h4>
                <select id="permintaan-klinik">
                    <option value="ALL" selected>ALL</option>
                    <option value="Reguler">REGULER</option>
                    <option value="Konsinyasi">KONSINYASI</option>
                </select>
                <div>
                    <table class="table table-bordered table-hover" id="permintaan-klinik-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Klinik</th>
                                <th>Jumlah Permintaan</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>List Pengiriman Klinik</h4>
                <select id="pengiriman-klinik">
                    <option value="ALL" selected>ALL</option>
                    <option value="1">REGULER</option>
                    <option value="2">KONSINYASI</option>
                </select>
                <div>
                    <table class="table table-bordered table-hover" id="pengiriman-klinik-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Klinik</th>
                                <th>Jumlah Pengiriman</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>Penjualan Klinik</h4>
                <select id="penjualan-klinik-doc">
                    <option value="ALL" selected>ALL</option>
                    <option value="1">REGULER</option>
                    <option value="5">KONSINYASI</option>
                </select>
                <div>
                    <table class="table table-bordered table-hover" id="penjualan-klinik-doc-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No DOC/SO</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <h4>SO</h4>
                <div>
                    <table class="table table-bordered table-hover" id="so-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Wilayah</th>
                                <th>Jumlah SO</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>SP</h4>
                <select id="sp">
                    <option value="ALL" selected>ALL</option>
                </select>
                <div>
                    <table class="table table-bordered table-hover" id="sp-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Supplier</th>
                                <th>Jumlah SP</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>Mitra</h4>
                <div>
                    <table class="table table-bordered table-hover" id="mitra-list">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Wilayah</th>
                                <th width="50">Jumlah Mitra</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <h4>SO Klinik</h4>
                <div>
                    <table class="table table-bordered table-hover" id="so-klinik-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Klinik</th>
                                <th>Jumlah SO</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <h4>Obat Terlaris</h4>
                <select id="obat-terlaris">
                    <option value="ALL" selected>ALL</option>
                    <option value="1">REGULER</option>
                    <option value="2">KONSINYASI</option>
                </select>
                <div>
                    <table class="table table-bordered table-hover" id="obat-terlaris-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID - Nama Obat</th>
                                <th>Supplier</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="3"></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>           
            <div class="col-md-4">
                <h4>Supplier</h4>
                <div>
                    <table class="table table-bordered table-hover" id="supplier-list">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Supplier</th>
                                <th>Jumlah SKU</th>
                                <th>SKU Tersedia</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="2"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div> 
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Jadwal SO Klinik</h4>
                <div>
                    <table class="table table-bordered table-hover" id="so-klinik-schedule-list">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Klinik</th>
                                <th>Jadwal SO</th>
                                <th>Jumlah Obat</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="3"></th>
                            <th></th>
                            <th></th>
                        </tfoot>
                    </table>
                </div>                
            </div>
        </div> --}}
@endsection

@section('extra-css')
    <style scoped>
        .content_header{
            display: flex;
            justify-content: space-between;
        }

        .table{
            width: 100% !important;
        }

        .fw-bold{
            font-weight: bold;
        }

        .select2-container{
            width: 100% !important;
            margin-bottom: 1rem;
        }

        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da !important;
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
        $(document).ready(async function() {
            let filterStart = '{{ $filterStart == '' ? 'ALL' : $filterStart }}'
            let filterEnd = '{{ $filterEnd == '' ? 'ALL' : $filterEnd }}'

            let dynamicColors = function(partlyInvisible = false) {
                var r = Math.floor(Math.random() * 255);
                var g = Math.floor(Math.random() * 255);
                var b = Math.floor(Math.random() * 255);

                if(partlyInvisible){
                    return "rgb(" + r + "," + g + "," + b + ", 0.3)"
                }

                return "rgb(" + r + "," + g + "," + b + ")";
            };

            let assetGudangChart = $('#asset-gudang-chart')
            let penjualanChart = $('#penjualan-list-chart')
            let penjualanPembelianChart = $('#penjualan-pembelian-list-chart')

            async function initAssetGudangDT(){
                let datas = []
                let labels = []
                let colors = []

                await $.ajax({
                    url: "{{ route('dashboard.asset-gudang') }}",
                    method: "GET",
                    data: {
                        "filterStart": filterStart,
                        "filterEnd": filterEnd,
                    },
                    success: function(response){
                        response.forEach(x => {
                            datas.push(x.total_aset_raw)
                            labels.push(x.jenis + " (" + x.total_aset + ") (" + x.jumlah_obat + " Item)")
                            colors.push(dynamicColors())
                        })
                    }
                })

                assetGudangChart = new Chart(assetGudangChart, {
                    type: 'pie',
                    data: {
                        datasets: [
                            {
                                data: datas,
                                backgroundColor: colors,
                                borderDisplay: false
                            }
                        ],
                        labels: labels,
                    },
                    options: {
                        plugins: {
                            legend: {
                                align: 'start'
                            }
                        }
                    }
                });
            }

            async function initPenjualanDT(){
                let datasTotal = []
                let datasReguler = []
                let datasSO = []
                let months = []

                await $.ajax({
                    url: "{{ route('dashboard.penjualan') }}",
                    type: "GET",
                    success: function(response){
                        response.forEach(x => {
                            months.push(x.label)
                            datasTotal.push(x.totalAmount)
                            datasReguler.push(x.regulerAmount)
                            datasSO.push(x.soAmount)
                        })

                        penjualanChart = new Chart(penjualanChart, {
                            type: 'bar',  // Default type
                            data: {
                                labels: months,
                                datasets: [
                                    {
                                        type: 'line',
                                        label: 'Total Penjualan',
                                        fill: false,
                                        data: datasTotal,
                                        borderColor: 'rgb(135,206,250)'
                                    },
                                    {
                                        type: 'bar',
                                        label: 'Penjualan Reguler',
                                        data: datasReguler,
                                        borderColor: dynamicColors(true),
                                        backgroundColor: 'rgb(255,127,80, 0.5)',
                                    },
                                    {
                                        type: 'bar',
                                        label: 'Stock Opname',
                                        data: datasSO,
                                        borderColor: dynamicColors(true),
                                        backgroundColor: 'rgb(139,0,139, 0.5)',
                                    }
                                ],
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                })
            }

            async function initPembelianPenjualanDT(){
                let datasPenjualan = []
                let datasPembelian = []
                let months = []

                await $.ajax({
                    url: "{{ route('dashboard.penjualan-pembelian') }}",
                    type: "GET",
                    success: function(response){
                        response.forEach(x => {
                            months.push(x.label)
                            datasPenjualan.push(x.penjualanAmount)
                            datasPembelian.push(x.pembelianAmount)
                        })

                        penjualanPembelianChart = new Chart(penjualanPembelianChart, {
                            type: 'bar',
                            data: {
                                labels: months,
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: 'Total Penjualan',
                                        data: datasPenjualan,
                                        borderColor: dynamicColors(),
                                        backgroundColor: dynamicColors()
                                    },
                                    {
                                        type: 'bar',
                                        label: 'Total Pembelian',
                                        data: datasPembelian,
                                        borderColor: dynamicColors(),
                                        backgroundColor: dynamicColors()
                                    },
                                ],
                            }
                        });
                    }
                })
            }

            function number_format(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            await initAssetGudangDT()
            await initPenjualanDT()
            await initPembelianPenjualanDT()
        })

    </script>
@endsection
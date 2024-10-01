@php
    function getBentukSediaan($input)
    {
        $parts = explode(',', explode(' ', $input)[1]);
        if (isset($parts[0])) {
            $term = trim($parts[0]);
            return $term;
        }
        return '';
    }

    function getSpokenNumber($number)
    {
        $ones = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($number < 12) {
            return $ones[$number];
        } elseif ($number < 20) {
            return $ones[$number - 10] . ' Belas';
        } elseif ($number < 100) {
            return $ones[floor($number / 10)] . ' Puluh ' . $ones[$number % 10];
        } elseif ($number < 200) {
            return 'Seratus ' . getSpokenNumber($number - 100);
        } elseif ($number < 1000) {
            return $ones[floor($number / 100)] . ' Ratus ' . getSpokenNumber($number % 100);
        } else {
            return 'Number out of range';
        }
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body, *{
            font-family: Arial
        }

        #kopSurat{
            overflow: auto;
            border-bottom: 3px solid #305496;
            padding-bottom: .25em;
        }

        #logoKop, #judulSurat{
            float: left;
            width: 30%;
        }

        #logoKop img{
            width: 100%;
        }

        #alamatKop{
            float: right;
            width: 35%;
        }

        h6{
            font-size: 10px;
            margin-bottom: 0;
        }

        .p2{
            font-size: 9px;
            margin-bottom: 0;
        }

        .p1{
            font-size: 9px;
            margin-top: 0;
            margin-bottom: 0;
        }

        #titleSurat{
            overflow: auto;
        }

        #tanggalSurat{
            float: right;
            width: 20%;
        }

        .paragraph{
            margin-top: 1.5em;
        }

        .textTable, .itemTable{
            border-collapse: collapse;
        }

        .textTable td{
            width: 180px;
        }

        .textTable, .textTable tr, .textTable tr td{
            font-size: 9px;
            margin-top: 0;
            margin-bottom: 0;
            border: 0;
        }

        .itemTable{
            width: 100%;
            box-sizing: border-box;
            margin-top: .25em;
        }
        
        .itemTable tr th, .itemTable tr td{
            border: 1px groove black;
            font-size: 9px;
            margin-top: 0;
            margin-bottom: 0;
            padding: 0.25em .75em .15em .75em;
        }
    </style>
</head>
<body>
    <div id="kopSurat">
        <div id="logoKop">
            <img src="{{ public_path('mediklik.png') }}" alt="Logo Mediklik">
        </div>
        <div id="alamatKop">
            <h6><b>KLINIK PRATAMA SYNAPSA MEDIKA</b></h6>
            <p class="p1">Ruko Bona Indah Business Center</p>
            <p class="p1">JL. Karang Tengah Raya No.8B, Lb. Bulus, Cilandak</p>
            <p class="p1">Jakarta | Telp : 021-22760445</p>
        </div>
    </div>

    <div id="titleSurat">
        <div id="judulSurat">
            <h6 style="margin-top: .75em"><b>Surat Pesanan Obat</b></h6>
            <p class="p1">No. SP : {{ $passedData->document_number }}</p>
        </div>
        <div id="tanggalSurat">
            <p class="p2">Jakarta, {{ $passedData->tanggal_pembelian }}</p>
        </div>
    </div>

    <div class="paragraph">
        <p class="p1">Melalui surat ini, yang bertandatangan di bawah ini:</p>
        <table class="textTable">
            <tr>
                <td>Nama</td>
                <td>: {{ $passedData->nama_petugas ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: {{ $passedData->jabatan_petugas ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. SIPA</td>
                <td>: {{ $passedData->sipa_petugas ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="paragraph">
        <p class="p1">Mengajukan pesanan obat pada:</p>
        <table class="textTable">
            <tr>
                <td>Nama PBF</td>
                <td>: {{ $passedData->supplier->name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $passedData->supplier->address }}</td>
            </tr>
            <tr>
                <td>No. Telp</td>
                <td>: {{ $passedData->supplier->phone }}</td>
            </tr>
        </table>
    </div>

    <div class="paragraph">
        <p class="p1">
            Dengan rincian pesanan sebagai berikut:
        </p>
        <table class="itemTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Obat</th>
                    <th>Bentuk Sediaan</th>
                    <th>Kekuatan/Dosis</th>
                    <th>Qty (angka dan terbilang)</th>
                    <th>Satuan</th>
                    <th>Harga Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($passedData->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name . ' (' . $item->supplier->name . ')', }}</td>
                    <td style="text-align: center;">{{ getBentukSediaan($item->packaging) }}</td>
                    <td>{{ $item->content }}</td>
                    <td>{{ $item->pivot->quantity . ' ( ' . getSpokenNumber($item->pivot->quantity) . ' )' }}</td>
                    <td style="text-align: center;">{{ $item->unit }}</td>
                    <td></td>
                    {{-- <td>{{ 'Rp'.number_format($item->pivot->total, 2) }}</td> --}}
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="paragraph">
        <p class="p1">Obat tersebut akan digunakan untuk memenuhi kegiatan kefarmasian di:</p>
        <table class="textTable">
            <tr>
                <td>Nama Klinik/Apotek</td>
                <td>: {{ $passedData->partner->name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $passedData->partner->address }}</td>
            </tr>
            <tr>
                <td>No. Telp</td>
                <td>: {{ $passedData->partner->phone }}</td>
            </tr>
        </table>
    </div>
    <div class="paragraph">
        <p class="p1">Hormat kami,</p>
        <p class="p1">Apoteker Pengelola</p>
    </div>
    <div class="paragraph" style="margin-top: 3.5em;">
        <p class="p1">{{ $passedData->nama_petugas ?? '-' }}</p>
        <p class="p1">{{ $passedData->sipa_petugas ?? '-' }}</p>
    </div>
</body>
</html>


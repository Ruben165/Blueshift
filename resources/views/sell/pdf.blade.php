<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body, *{
            font-family: Tahoma !important;
        }

        #kopSurat{
            overflow: auto;
            padding-bottom: .25em;
        }

        #logoKop{
            float: left;
            width: 45%;
        }

        #logoKop img{
            width: 100%;
        }

        #alamatKop{
            float: right;
            width: 45%;
        }

        h4{
            margin-bottom: 0;
        }

        h6{
            margin-bottom: 0;
        }

        .p2{
            margin-bottom: 0;
        }

        .p1{
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
            margin-top: .5em;
        }

        .textTable, .itemTable{
            border-collapse: collapse;
        }

        .textTable, .textTable tr, .textTable tr td{
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
            border: 1px solid black;
            margin-top: 0;
            margin-bottom: 0;
            padding: 0.25em .75em .15em .75em;
        }

        .itemTable tr th{
            background-color: #4472c4;
            color: white;
        }

        .paragraph-spacer{
            border: 1px solid black;
            width: 100%;
            height: 2em;
            margin-top: 1em;
            padding: 0 .75em;
        }

        .paragraph-column{
            width: 100%;
            text-align: center;
            margin-top: 1.5em;
        }

        .column3{
            width: 33%;
        }

        .spacer{
            color: white;
        }

        .judulSurat{
            float: left;
            width: 50%;
        }

        .statusKode{
            float: right;
            width: 40%;
            text-align: right;
        }
    </style>
</head>
<body>
    <div id="kopSurat">
        <div id="logoKop">
            <img src="{{ public_path('images/logo/icon.png') }}" alt="Logo Blueshift">
        </div>
        <div id="alamatKop">
            <div>
                <div class="judulSurat">
                    <h4><b>SURAT JALAN</b></h4>
                </div>
                {{-- <div class="7 --}}
            </div>
            <table class="textTable" style="font-size: 7pt;">
                <tr>
                    <td style="width: 8em;">ID Pengiriman</td>
                    <td>: {{ $passedData->document_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="width: 8em;">ID Permintaan</td>
                    <td>: {{ $passedData->id_request ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="width: 8em;">Tanggal Kirim</td>
                    <td>: {{ $passedData->formated_created_at }}</td>
                </tr>
                <tr>
                    <td style="width: 8em;">Nama Klinik</td>
                    <td>: {{ $passedData->destinationPartner->name }}</td>
                </tr>
                <tr>
                    <td style="width: 8em;">Pengantar</td>
                    <td>:</td>
                </tr>
                <tr>
                    <td style="color: white;">_</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Hal. {{ $currentPage }} / {{ $totalPage }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="paragraph">
        <table class="itemTable">
            <thead>
                <tr>
                    <th style="font-size: 6pt; font-family: Tahoma;">No.</th>
                    <th style="font-size: 6pt; font-family: Tahoma;">ID Database</th>
                    <th style="font-size: 6pt; font-family: Tahoma;">Nama Barang</th>
                    <th style="font-size: 6pt; font-family: Tahoma;">Satuan</th>
                    <th style="font-size: 6pt; font-family: Tahoma;">Batch-Exp</th>
                    <th style="font-size: 6pt; font-family: Tahoma;">Qty</th>
                    <th style="font-size: 6pt; font-family: Tahoma;">GOL</th>
                    <th style="font-size: 6pt; font-family: Tahoma; width: 2em;">VERIF 1</th>
                    <th style="font-size: 6pt; font-family: Tahoma; width: 2em;">VERIF 2</th>
                </tr>
            </thead>
            <tbody>
                @foreach($passedData->partnerItems as $index => $item)
                    @if($index >= $startNo && $index <= $lastNo)
                        <tr>
                            <td style="font-size: 6pt; font-family: Tahoma;">{{ $index + 1 }}</td>
                            <td style="font-size: 6pt; font-family: Tahoma;">{{ $item->barcode_id }}</td>
                            <td style="font-size: 6pt; font-family: Tahoma;">{{ strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')') }}</td>
                            <td style="text-align: center; font-size: 6pt; font-family: Tahoma;">{{ strtoupper($item->item->unit) }}</td>
                            <td style="font-size: 6pt; font-family: Tahoma;">{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
                            <td style="text-align: center; font-size: 6pt; font-family: Tahoma;">{{ $item->pivot->quantity }}</td>
                            <td style="font-size: 6pt; font-family: Tahoma;">{{ strtoupper($item->item->type->name) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    @if($isLast == 1)
    <div class="paragraph-spacer">
        <p style="font-size: 7pt; font-family: Tamoha;">{{ $passedData->description }}</p>
    </div>

    <table class="paragraph-column" style="font-size: 7pt; font-family: Tahoma;">
        <tr>
            <td class="column3">
                <h6 style="font-size: 7pt; font-family: Tahoma;">Barang diterima,</h6>
            </td>
            <td>
                <h6 style="font-size: 7pt; font-family: Tahoma;">Pengirim,</h6>
            </td>
            <td>
                <h6 style="font-size: 7pt; font-family: Tahoma;">Disetujui,</h6>
            </td>
        </tr>
        <tr>
            <td>
                <p class="p1">(Verifikasi 2)</p>
            </td>
            <td>
                <p class="p1">(Verifikasi 1)</p>
            </td>
        </tr>
        <tr>
            <td class="spacer">
                |
                <br>
                |
                <br>
            </td>
        </tr>
        <tr>
            <td>
                <p class="p1">(..................................)</p>
            </td>
            <td>
                <p class="p1">(..................................)</p>
            </td>
            <td>
                <p class="p1">(..................................)</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="p1">*Nama dan Cap Perusahaan</p>
            </td>
            <td>

            </td>
            <td>
                <p class="p1">apt.<span class="spacer">__________________</span></p>
            </td>
        </tr>
    </table>
    @endif
</body>
</html>


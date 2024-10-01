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
            width: 30%;
        }

        #logoKop img{
            width: 100%;
        }

        #alamatKop{
            float: right;
            width: 45%;
        }

        h4{
            font-size: 7pt;
            margin-bottom: 0;
        }

        h6{
            font-size: 7pt;
            margin-bottom: 0;
        }

        .p2{
            font-size: 7pt;
            margin-bottom: 0;
        }

        .p1{
            font-size: 7pt;
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
            font-size: 7pt;
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
            font-size: 7pt;
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
            height: 3em;
            margin-top: 1em;
        }

        .paragraph-column{
            width: 100%;
            text-align: center;
            margin-top: 1.5em;
        }

        .column2{
            width: 49%;
        }

        .spacer{
            color: white;
        }

        .judulSurat{
            float: left;
            width: 60%;
        }

        .statusKode{
            float: right;
            width: 40%;
            text-align: right;
        }
    </style>
</head>
<body style="font-family: Tahoma !important">
    <div id="kopSurat">
        <div id="logoKop">
            <img src="{{ public_path('images/logo/icon.png') }}" alt="Logo Blueshift">
        </div>
        <div id="alamatKop">
            <div>
                <div class="judulSurat">
                    <h4><b>STOK OPNAME</b></h4>
                </div>
            </div>
            <table class="textTable">
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ $formated_date }}</td>
                </tr>
                <tr>
                    <td>Nama Klinik</td>
                    <td>: {{ $partner->name }}</td>
                </tr>
                <tr>
                    <td>PIC Checker</td>
                    <td>:</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="paragraph">
        <table class="itemTable">
            <thead>
                <tr>
                    <th style="width:12%;">ID Database</th>
                    <th style="width:30%;">Nama Barang</th>
                    <th style="width:7%;">Satuan</th>
                    <th>Batch-Exp</th>
                    <th>GOL</th>
                    <th style="width: 7%;">Qty Awal</th>
                    <th style="width: 8%;">Qty Update</th>
                    <th style="width: 8%;">Terjual</th>
                    <th style="width: 7%;">Verif 1</th>
                    <th style="width: 7%;">Verif 2</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partnerItems as $item)
                <tr>
                    <td>{{ $item->barcode_id }}</td>
                    <td>{{ strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')') }}</td>
                    <td style="text-align: center;">{{ strtoupper($item->item->unit) }}</td>
                    <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
                    <td style="text-align: center;">{{ strtoupper($item->item->type->name) }}</td>
                    <td style="text-align: center;">{{ !isset($isHasilSO) ? $item->stock_qty : ($item->pivot->quantity + $item->pivot->quantity_left) }}</td>
                    <td style="text-align: center;">{{ !isset($isHasilSO) ? '' : $item->pivot->quantity_left }}</td>
                    <td style="text-align: center;">{{ !isset($isHasilSO) ? '' : $item->pivot->quantity }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="paragraph-column">
        <tr>
            <td class="column2">
                <h6 style="font-size: 7pt;">PIHAK KLINIK,</h6>
            </td>
            <td>
                <h6 style="font-size: 7pt;">PIC CHECKER,</h6>
            </td>
        </tr>
        <tr>
            <td>
                <p class="p1" style="font-size: 7pt;">(Verifikasi 2)</p>
            </td>
            <td>
                <p class="p1" style="font-size: 7pt;">(Verifikasi 1)</p>
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
                <p class="p1" style="font-size: 7pt;">(..................................)</p>
            </td>
            <td>
                <p class="p1" style="font-size: 7pt;">(..................................)</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="p1" style="font-size: 7pt;">*Nama dan Cap Perusahaan</p>
            </td>
            <td>

            </td>
        </tr>
    </table>
</body>
</html>


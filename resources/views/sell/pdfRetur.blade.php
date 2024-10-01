<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body, *{
            font-family: Arial
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
            font-size: 20px;
            margin-bottom: 0;
        }

        h6{
            font-size: 11px;
            margin-bottom: 0;
        }

        .p2{
            font-size: 11px;
            margin-bottom: 0;
        }

        .p1{
            font-size: 11px;
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
            font-size: 12px;
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
            font-size: 9px;
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
            margin-top: 1em;
        }

        .paragraph-column{
            width: 100%;
            text-align: center;
            margin-top: 1.5em;
        }

        .column2{
            width: 25%;
        }

        .columnDivider{
            width: 50%;
        }

        .spacer{
            color: white;
        }
    </style>
</head>
<body>
    <div id="kopSurat">
        <div id="logoKop">
            <img src="{{ public_path('images/logo/icon.png') }}" alt="Logo Mediklik">
        </div>
        <div id="alamatKop">
            <h4><b>DOKUMEN KONFIRMASI</b></h4>
            <table class="textTable">
                <tr>
                    <td>No. Surat</td>
                    <td>: {{ @$passedData->document_number }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ @$passedData->returned_at ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Nama Klinik</td>
                    <td>: {{ $passedData->sourcePartner->name }}</td>
                </tr>
                <tr>
                    <td>Petugas</td>
                    <td>: {{ @$passedData->pic_retur ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="paragraph">
        <table class="itemTable">
            <thead>
                <tr>
                    <th>ID Database</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Stok</th>
                    <th>Stok Retur</th>
                    <th>Stok Sisa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($passedData->partnerItems as $item)
                @php
                    $itemSelected = App\Models\PartnerItem::where('partner_id', $passedData->source_partner_id)
                                        ->where('item_id', $item->item_id)
                                        ->where('barcode_id', $item->barcode_id)
                                        ->first();
                    $stok = $itemSelected->stock_qty;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $item->barcode_id }}</td>
                    <td>{{ strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')') }}</td>
                    <td style="text-align: center;">{{ strtoupper($item->item->unit) }}</td>
                    <td style="text-align: center;">{{ $item->pivot->quantity_left + $item->pivot->quantity }}</td>
                    <td style="text-align: center;">{{ $item->pivot->quantity }}</td>
                    <td style="text-align: center;">{{ $item->pivot->quantity_left }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- <div class="paragraph-spacer" style="padding:0.25em">
        <p class="p1">Data ini merupakan jumlah penyesuaian persediaan barang baik yang diretur oleh Pihak Mitra Klinik atau ditarik oleh Pihak Mediklik</p>
    </div> --}}

    {{-- <table class="paragraph-column">
        <tr>
            <td class="column2">
                <h6>Disetujui,</h6>
            </td>
            <td class="columnDivider">
            </td>
            <td class="column2">
                <h6>Petugas,</h6>
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
            <td></td>
            <td>
                <p class="p1">(..................................)</p>
            </td>
        </tr>
    </table> --}}
</body>
</html>


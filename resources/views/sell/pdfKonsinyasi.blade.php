<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body, *{
            font-family: Arial
        }

        .kopSurat{
            overflow: auto;
            padding-bottom: .25em;
        }

        .logoKop{
            float: left;
            width: 45%;
        }

        .logoKop img{
            width: 100%;
        }

        .alamatKop{
            float: right;
            width: 45%;
        }

        h4{
            font-size: 18px;
            margin-bottom: 0;
        }

        h6{
            font-size: 10px;
            margin-bottom: 0;
        }

        .no-top-margin{
            margin-top: 0;
        }

        .p2{
            font-size: 10px;
            margin-bottom: 0;
        }

        .p1{
            font-size: 10px;
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

        .paragraph-center{
            margin-top: 1.5em;
            text-align: center;
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
            font-size: 10px;
            margin-top: 0;
            margin-bottom: 0;
            padding: 0.25em .75em .15em .75em;
        }

        .itemTable tr th{
            background-color: #4472c4;
            color: white;
        }

        .no-border{
            border: 0 solid black;
            color: white;
        }

        .no-border2{
            border: 0 solid black;
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

        .column3{
            width: 33%;
        }

        .spacer{
            color: white;
        }

        .bg-blue{
            background-color: #4472c4;
            color: white;
            padding: 0.1em .5em;
            margin-bottom: .25em;
        }

        .dateTable{
            height: 4em;
            text-align: center;
            vertical-align: bottom;
        }

        #cap{
            height: 7em;
        }
    </style>
</head>
<body>
    <div class="kopSurat">
        <div class="logoKop">
            <img src="{{ public_path('mediklik.png') }}" alt="Logo Mediklik">
        </div>
        <div class="alamatKop">
            <h4><b>PROFORMA INVOICE @if($passedData->sell_order_type_id != 5)TRANSFER @endif</b></h4>
            <table class="textTable">
                <tr>
                    <td>Number Invoice</td>
                    <td>: {{ $passedData->document_number }}</td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>: {{ $passedData->formated_created_at }}</td>
                </tr>
                <tr>
                    <td>Due Date</td>
                    <td>: {{ $passedData->formated_created_at ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="kopSurat">
        <div class="logoKop">
            <div class="paragraph">
                @if($passedData->sell_order_type_id == 2)
                <h6 class="bg-blue no-top-margin">Vendor</h6>
                <p class="p1">Klinik Synapsa Medika</p>
                <p class="p1">Bona Indah Bisnis Center</p>
                <p class="p1">Jl. Karang Tengah Raya No.8B, RT.7/RW.6</p>
                <p class="p1">Jakarta Selatan 12440, DKI Jakarta, Indonesia</p>
                <p class="p1">mediklik.synapsahealth@gmail.com</p>
                @else
                <h6 class="bg-blue no-top-margin">Transfer From</h6>
                <p class="p1">Klinik {{ $passedData->sourcePartner->name }}</p>
                <p class="p1">{{ $passedData->sourcePartner->address }}</p>
                <p class="p1">{{ $passedData->sourcePartner->phone }}</p>
                @endif
            </div>
        </div>
        <div class="alamatKop">
            <div class="paragraph">
                <h6 class="bg-blue no-top-margin">Ship To</h6>
                <p class="p1">{{ $passedData->destinationPartner->name }}</p>
                <p class="p1">{{ $passedData->destinationPartner->address }}</p>
                <p class="p1">{{ $passedData->destinationPartner->phone }}</p>
            </div>
        </div>
    </div>

    <div class="paragraph">
        <table class="itemTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name Description</th>
                    <th>Batch-Exp</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($passedData->partnerItems as $index => $item)
                    @if($item->pivot->quantity != 0)
                        <tr>
                            <td style="text-align: center;">{{ (int) $index + 1 }}</td>
                            <td>{{ strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')') }}</td>
                            <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
                            <td style="text-align: center;">{{ $item->pivot->quantity }}</td>
                            <td style="text-align: right;">{{ $item->pivot->quantity != 0 ? 'Rp '.number_format((float) $item->pivot->total / (float) $item->pivot->quantity, 2) : 'Rp 0.00' }}</td>
                            <td style="text-align: right;">{{ $item->pivot->quantity != 0 ? 'Rp'.number_format($item->pivot->quantity * $item->pivot->total / $item->pivot->quantity, 2) : 'Rp 0.00' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="no-border"><td colspan="6" class="no-border">|</td></tr>
                <tr>
                    <th colspan="2">Notes and Instruction</th>
                    <td rowspan="8"></td>
                    <td colspan="2">SubTotal</td>
                    <td style="text-align: right;">{{ 'Rp'.number_format($passedData->total_price, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2">Pembayaran dapat di transfer ke</td>
                    <td colspan="2">PPN 11%</td>
                    <td style="text-align: right;">{{ 'Rp'.number_format($passedData->total_price * 11 / 100, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2">Rekening A/N:</td>
                    <td colspan="2"><b>Sub Total</b></td>
                    <td style="text-align: right;"><b>{{ 'Rp'.number_format($passedData->total_price + ($passedData->total_price * 11 / 100), 2) }}</b></td>
                </tr>
                <tr>
                    <td colspan="2">BJB</td>
                    <td colspan="3" rowspan="4" style="text-align: center">
                        <img id="cap" src="{{ public_path('cap_synapsa.png') }}" alt="Cap Mediklik">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">PT Synapsa Medikatama Indonesia</td>
                </tr>
                <tr>
                    <td colspan="2">ACC No. 0131050550002</td>
                </tr>
                <tr>
                    <td colspan="2" class="dateTable">{{ $passedData->formated_created_at }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center">Date</td>
                    <td colspan="3" style="text-align: center;">Authorized Signature</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="paragraph-center">
        <p class="p1">Plaza Aminta, 7th floor, Suite 710</p>
        <p class="p1">Jalan TB Simatupang kav. 10, Jakarta 12310</p>
        <p class="p1">Phone : 021 - 751 2010</p>
    </div>
</body>
</html>



@php
    $isUmum = count($prices['Umum']) != 0;
    $isKhusus = count($prices['Khusus']) != 0;
    $isPrekursor = count($prices['Prekursor']) != 0;
    $isAlkes = count($prices['Alkes']) != 0;
    $isObatTertentu = count($prices['Obat Obat Tertentu']) != 0;

    $types = array_keys($totalSum);
    
    $currentDate = Carbon\Carbon::now();

    $month = $currentDate->format('F');

    switch ($month) {
        case 'January':
            $month = 'JANUARI';
            break;
        case 'February':
            $month = 'FEBRUARI';
            break;
        case 'March':
            $month = 'MARET';
            break;
        case 'April':
            $month = 'APRIL';
            break;
        case 'May':
            $month = 'MEI';
            break;
        case 'June':
            $month = 'JUNI';
            break;
        case 'July':
            $month = 'JULI';
            break;
        case 'August':
            $month = 'AGUSTUS';
            break;
        case 'September':
            $month = 'SEPTEMBER';
            break;
        case 'October':
            $month = 'OKTOBER';
            break;
        case 'November':
            $month = 'NOVEMBER';
            break;
        case 'December':
            $month = 'DESEMBER';
            break;
    }
@endphp
<body style="font-family: Arial, Helvetica, sans-serif">
    <table style="border-collapse: collapse;">
        <tr>
            <th colspan="4" style="text-align: center; font-size: 24pt; font-weight: bold;">{{ $sellOrder->destinationPartner->name }}</th>
        </tr>

        @foreach($prices as $index => $price)
            @php
                $currentType = $index;
            @endphp

            @if((!$isUmum && $currentType == 'Umum') || (!$isKhusus && $currentType == 'Khusus') || (!$isPrekursor && $currentType == 'Prekursor') || (!$isObatTertentu && $currentType == 'Obat Obat Tertentu') || (!$isAlkes && $currentType == 'Alkes'))
            @else
                <tr>
                    <th style="font-size: 10pt; font-weight: bold; text-align: left;" colspan="4">
                        COUNT SKU = {{ count($prices[$currentType]) }}
                    </th>
                </tr>
                <tr>
                    <th style="font-size: 10pt; font-weight: bold; text-align: left;" colspan="3">
                        SUM QTY = {{ $totalSum[$currentType] }}
                    </th>
                    <th style="font-size: 10pt; font-weight: bold; text-align: right;">{{ $isAll ? 'ALL' : $sellOrder->status_kode }}</th>
                </tr>
                <tr>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt; font-weight: bold; text-align: left;">
                        HARGA BERLAKU PER {{ $month }} {{ $currentDate->format('Y') }}
                    </th>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt; font-weight: bold; text-align: center; background-color: #d9d9d9;" colspan="2">
                        SUB TOTAL
                    </th>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt; font-weight: bold; background-color: #d9d9d9;">
                        {{ number_format($priceType[$currentType], 0) }}
                    </th>
                </tr>
                <tr>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em; width: 60em' : '' }}; font-size: 10pt; font-weight: bold; text-align: center; background-color: #d9d9d9;">
                        NAMA OBAT {{ strtoupper($currentType) }}
                    </th>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em; width: 3em;' : '' }}; font-size: 10pt; font-weight: bold; text-align: center; background-color: #d9d9d9;">
                        QTY
                    </th>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em; width: 6em' : '' }}; font-size: 10pt; font-weight: bold; text-align: center; background-color: #d9d9d9;">
                        HARGA
                    </th>
                    <th style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em; width: 7em' : '' }}; font-size: 10pt; font-weight: bold; text-align: center; background-color: #d9d9d9;">
                        TOTAL
                    </th>
                </tr>
                @foreach($price as $theItem)
                    <tr>
                        <td style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt;">
                            {{ $theItem['name'] }}
                        </td>
                        <td style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt; text-align: center;">
                            {{ $theItem['stock_qty'] }}
                        </td>
                        <td style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt; text-align: right;">
                            {{ number_format($theItem['price'], 0) }}
                        </td>
                        <td style="border: 1px solid black; {{ $typeExport == 'pdf' ? 'padding: 0.15em 0.5em' : '' }}; font-size: 10pt; text-align: right;">
                            {{ number_format($theItem['price'] * $theItem['stock_qty'], 0) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td style="padding-top: 1em;"></td>
                </tr>
            @endif
        @endforeach
    </table>
</body>
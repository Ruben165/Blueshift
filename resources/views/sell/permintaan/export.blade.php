
<body style="font-family: Arial, Helvetica, sans-serif;">
    <table style="border-collapse: collapse;">
        <tr>
            <th colspan="2" style="background-color: #4472c4; font-size: 10pt; color: white; font-weight: bold; text-align:left;{{ $typeExport == 'pdf' ? 'border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">NAMA KLINIK</th>
        </tr>
        <tr>
            <td colspan="2" style="{{ $typeExport == 'pdf' ? 'font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em; border: 1px solid black; height: 2em;' : 'font-size: 10pt;' }}">{{ $consignmentRequest->partner->name }}</td>
        </tr>
        <tr><td style="padding: .5em;"></td></tr>
        <tr>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">NO</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">ID MEDIKLIK</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">NAMA OBAT</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">ISI</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">FABRIK</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">QTY REQ</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">QTY READY</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">QTY BUFFER</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">ID PENGIRIM</th>
            <th style="background-color: #4472c4; color: white; font-size: 9pt; font-weight: bold; text-align:center;{{ $typeExport == 'pdf' ? 'border: 1px solid black; margin-top: 0; margin-bottom: 0; padding: 0.5em .75em .35em .75em;' : '' }}">PIC</th>
        </tr>
        @foreach($consignmentRequest->items as $index => $item)
            <tr>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $index + 1 }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->sku }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->name . ' (' . $item->content . ')' }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->packaging }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->manufacturer }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->pivot->quantity }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->pivot->quantity_send }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}"></td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->pivot->sender_id }}</td>
                <td style="{{ $typeExport == 'pdf' ? "border: 1px solid black; font-size: 10pt; margin-top: 0; margin-bottom: 0; padding: 0.25em .75em .15em .75em;" : "font-size: 10pt;"}}">{{ $item->pivot->sender_pic }}</td>
            </tr>
        @endforeach
    </table>
</body>
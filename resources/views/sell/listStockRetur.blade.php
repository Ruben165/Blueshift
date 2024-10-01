<body style="font-family: Arial, Helvetica, sans-serif">
    <h1 style="font-size: 24pt; font-weight: bold;">List Retur</h1>
    <h2 style="font-size: 10pt;">Klinik Sumber: {{ $partner->clinic_id . ' - ' . $partner->name }}</h2>

    <table style="border-collapse: collapse;">
        <tr>
            <th style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; background-color: #4472c4; font-size: 10pt; color: white;">No.</th>
            <th style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; background-color: #4472c4; font-size: 10pt; color: white;">SKU</th>
            <th style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; background-color: #4472c4; font-size: 10pt; color: white;">Nama Obat</th>
            <th style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; background-color: #4472c4; font-size: 10pt; color: white;">Kuantitas Aktual</th>
            <th style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; background-color: #4472c4; font-size: 10pt; color: white;">Kuantitas Retur</th>
        </tr>
        @foreach($partnerItems as $index => $partnerItem)
        <tr>
            <td style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; font-size: 10pt;">
                {{ $index+1 }}
            </td>
            <td style="border: 1px solid black; padding: 0.15em 0.5em; font-size: 10pt;">
                {{ $partnerItem->item_id }}
            </td>
            <td style="border: 1px solid black; padding: 0.15em 0.5em; font-size: 10pt;">
                {{ $partnerItem->item->name .' ('.$partnerItem->item->content.') ('.$partnerItem->item->packaging.') ('.$partnerItem->item->manufacturer.').' }}
            </td>
            <td style="text-align:center; border: 1px solid black; padding: 0.15em 0.5em; font-size: 10pt;">
                {{ $partnerItem->stock_qty }}
            </td>
            <td style="border: 1px solid black; padding: 0.15em 0.5em; font-size: 10pt;">
            </td>
        </tr>
        @endforeach
    </table>
</body>
<table class="itemTable">
    <thead>
        <tr>
            <th>Barcode</th>
            <th>ProductName</th>
            <th>UoM</th>
            <th>SellingPrice</th>
        </tr>
    </thead>
    <tbody>
        @foreach($partnerItems as $item)
        <tr>
            <td>{{ $item->barcode_id }}</td>
            <td>{{ strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')') }}</td>
            <td>{{ $partner->clinic_id }}</td>
            <td>{{ $item->stock_qty}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

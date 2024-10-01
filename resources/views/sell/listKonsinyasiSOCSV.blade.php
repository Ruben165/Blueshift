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
            <td>{{ $item->pivot->quantity_left }}</td>
            <td>{{ $item->pivot->quantity != 0 ? $item->pivot->total / $item->pivot->quantity : 0 }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

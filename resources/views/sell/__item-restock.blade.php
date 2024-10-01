<table class="table table-hover" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="150">Barcode Id</th>
        <th scope="col" width="300">Nama Item</th>
        <th scope="col" width="100">Kuantitas di Mitra Konsinyasi</th>
        <th scope="col" width="100">Kuantitas di Synapsa</th>
        <th scope="col" width="150">Batch-Exp</th>
        <th scope="col" width="150">Harga Barang di Synapsa
        <th scope="col" width="150">Kuantitas Restock</th>
      </tr>
    </thead>
    <tbody>
      @foreach($partnerItems as $index => $item)
        <input type="hidden" name="barcodeId[]" id="barcodeId[]" value="{{ $item->barcode_id }}">
        <tr>
          <td>{{ $item->barcode_id }}</td>
          <td>{{ $item->item->name .' ('.$item->item->content.') ('.$item->item->packaging.').' }}</td>
          <td>{{ $item->stock_qty }}</td>
          <td>{{ $item->stockSynapsa }}</td>
          <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
          <td>{{ 'Rp '.number_format((float) $item->discount_price, 2) }}</td>
          <td>
            <div class="form-group">
              <input type="number" name="refillQuantity[]" id="refillQuantity[]" class="form-control" min="0" value="0">
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
</table>
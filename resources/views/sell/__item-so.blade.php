<table class="table table-hover" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="150">Barcode Id</th>
        <th scope="col" width="300">Nama Item</th>
        <th scope="col" width="200">Satuan</th>
        <th scope="col" width="150">Batch-Exp</th>
        <th scope="col" width="200">Golongan</th>
        <th scope="col" width="100">Kuantitas Awal</th>
        <th scope="col" width="100">Kuantitas Update</th>
        <th scope="col" width="100">Kuantitas Terjual</th>
      </tr>
    </thead>
    <tbody>
      @php
        $totalFirst = 0;
        $totalUpdate = 0;
        $totalSold = 0;
      @endphp
      @foreach($partnerItems as $index => $item)
        @php
          $totalFirst += intval($item->stock_qty);

          if($isAlreadyImport){
            $totalUpdate += intval($item->stockUpdate);
            $totalSold += intval($item->stockTerjual);
          }
        @endphp
        <input type="hidden" name="barcodeId[]" id="barcodeId[]" value="{{ $item->barcode_id }}">
        <tr>
          <td>{{ $item->barcode_id }}</td>
          <td>{{ $item->item->name .' ('.$item->item->content.') ('.$item->item->packaging.') ('.$item->item->manufacturer.').' }}</td>
          <td>{{ strtoupper($item->item->unit) }}</td>
          <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
          <td>{{ strtoupper($item->item->type->name) }}</td>
          <td>{{ $item->stock_qty }}</td>
          <td>
              {{ $isAlreadyImport ? $item->stockUpdate : '0' }}
              <input type="hidden" name="soNewQuantity[]" id="soNewQuantity[]" class="form-control" value={{ $isAlreadyImport ? $item->stockUpdate : '0' }}>

          </td>
          <td>
              {{ $isAlreadyImport ? $item->stockTerjual : '0' }}
              <input type="hidden" name="soSoldQuantity[]" id="soSoldQuantity[]" class="form-control" value={{ $isAlreadyImport ? $item->stockTerjual : '0' }}>
          </td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="5">Total</th>
        <th>{{ $totalFirst }}</th>
        <th>{{ $totalUpdate }}</th>
        <th>{{ $totalSold }}</th>
      </tr>
    </tfoot>
</table>
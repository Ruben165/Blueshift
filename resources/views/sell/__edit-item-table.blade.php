<table class="table table-hover" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="25">No</th>
        <th scope="col" width="150">Barcode Id</th>
        <th scope="col" width="300">Nama Item</th>
        <th scope="col" width="100">Kuantitas {{ isset($sell) && $sell->sell_order_type_id == 5 ? 'Terjual' : 'Request' }}</th>
        <th scope="col" width="100">Kuantitas Aktual</th>
        @if($sell->sell_order_type_id == 5)
        <th scope="col" width="100">Kuantitas Tersisa Setelah SO</th>
        @endif
        <th scope="col" width="150">Batch-Exp</th>
        <th scope="col" width="150">Harga Barang</th>
        <th scope="col" width="150">Jumlah</th>
        <th scope="col" width="150">Action</th>
      </tr>
    </thead>
    <tbody>
      @if(isset($sell))
        @php
          $totalQtyRequest = 0;
          $totalQtyReal = 0;
        @endphp
        @foreach($sell->partnerItems as $index => $item)
          @php
            $totalQtyRequest += $item->pivot->quantity;
            $totalQtyReal += $item->stock_qty;
          @endphp
          <tr>
            <td>{{ (int) $index + 1 }}</td>
            <td>{{ $item->barcode_id }}</td>
            <td>{{ $item->item->name .' ('.$item->item->content.') ('.$item->item->packaging.') ('.$item->item->manufacturer.').' }}</td>
            @if($sell->sell_order_type_id == 5)
            <td>{{ $item->pivot->quantity + $item->pivot->quantity_left }}</td>
            @endif
            <td>{{ $item->pivot->quantity }}</td>
            @if(!isset($sell) || (isset($sell) && $sell->status_id != 2 && $sell->status_id != 4))
            <td>{{ $item->stock_qty }}</td>
            @elseif($sell->sell_order_type_id == 5)
            <td>{{ $item->pivot->quantity_left }}</td>
            @endif
            <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
            <td>{{ $item->pivot->quantity != 0 ? 'Rp '.number_format((float) $item->pivot->total / (float) $item->pivot->quantity, 2) : 'Rp 0.00' }}</td>
            <td><b>{{ 'Rp '.number_format($item->pivot->total, 2) }}</b></td>
            <td><div class="btn btn-danger btn-sm removeButton"><div style="display:flex; align-items:center;"><span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span></div></div></td>
          </tr>
        @endforeach
      @endif
    </tbody>
    <tfoot>
      <tr>
        @if($sell->sell_order_type_id != 5)
          <th colspan="3">Total</th>
        @else
          <th colspan="8">Total Harga</th>
        @endif
        @if(in_array($sell->sell_order_type_id, [1, 2, 3, 4]))
          <th id="totalQtyRequest">{{ $totalQtyRequest ?? 0 }}</th>
          <th id="totalQtyReal">{{ $totalQtyReal ?? 0 }}</th>
          <th colspan="2"></th>
        @endif
        <th id="totalPrice">{{ 'Rp '.number_format($sell->total_price, 2) }}</th>
      </tr>
    </tfoot>
</table>
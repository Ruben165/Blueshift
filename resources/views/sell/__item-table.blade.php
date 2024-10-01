<table class="table table-hover" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="25">No</th>
        <th scope="col" width="150">Barcode Id</th>
        <th scope="col" width="300">Nama Item</th>
        @if(isset($sell) && $sell->sell_order_type_id == 5)
        <th scope="col" width="100">Kuantitas Awal Sebelum SO</th>
        @endif
        <th scope="col" width="100">Kuantitas {{ isset($sell) && $sell->sell_order_type_id == 5 ? 'Terjual' : 'Kirim' }}</th>
        @if(!isset($sell) || (isset($sell) && $sell->status_id != 2 && $sell->status_id != 4))
        <th scope="col" width="100">Kuantitas Aktual</th>
        @elseif($sell->sell_order_type_id == 5)
        <th scope="col" width="100">Kuantitas Tersisa Setelah SO</th>
        @endif
        @if((isset($sell) && in_array($sell->sell_order_type_id, [1, 2]) && $sell->status_id == 1) || (isset($sell) && in_array($sell->sell_order_type_id, [1])))
        <th scope="col" width="100">Rak</th>
        @endif
        <th scope="col" width="150">Batch-Exp</th>
        <th scope="col" width="150">Harga Barang @if(isset($sell)) Saat Pemesanan @endif</th>
        <th scope="col" width="150">Jumlah</th>
        @if(!isset($sell))
        <th scope="col" width="150">Action</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @if(isset($sell))
        @php
          if($sell->sell_order_type_id != 5){
            $totalQtyRequest = 0;
            $totalQtyReal = 0;
          }
          else{
            $totalFirst = 0;
            $totalSold = 0;
            $totalUpdate = 0;
            $totalPriceAtOrder = 0;
          }
        @endphp
        @foreach($sell->partnerItems as $index => $item)
          @php
            if($sell->sell_order_type_id != 5){
              $totalQtyRequest += $item->pivot->quantity;
              $totalQtyReal += $item->stock_qty;
            }
            else{
              $totalFirst += $item->pivot->quantity + $item->pivot->quantity_left;
              $totalSold += $item->pivot->quantity;
              $totalUpdate += $item->pivot->quantity_left;
              $totalPriceAtOrder += $item->pivot->quantity != 0 ? ($item->pivot->total / $item->pivot->quantity) : 0;
            }
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
            @if((isset($sell) && in_array($sell->sell_order_type_id, [1, 2]) && $sell->status_id == 1) || (isset($sell) && in_array($sell->sell_order_type_id, [1])))
            <td>{{ $item->shelf->name ?? '-' }}</td>
            @endif
            <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
            <td>{{ $item->pivot->quantity != 0 ? 'Rp '.number_format((float) $item->pivot->total / (float) $item->pivot->quantity, 2) : 'Rp 0.00' }}</td>
            <th>{{ 'Rp '.number_format($item->pivot->total, 2) }}</th>
          </tr>
        @endforeach
      @endif
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3">Total</th>
        @if(!isset($sell))
          <th id="totalQtyRequest">0</th>
          <th id="totalQtyReal">0</th>
          <th colspan="2"></th>
        @else
          @if(in_array($sell->sell_order_type_id, [1, 2, 3, 4]))
            <th id="totalQtyRequest">{{ $totalQtyRequest ?? 0 }}</th>
            @if(!in_array($sell->sell_order_type_id, [3, 4]))
              @if($sell->status_id != 2)
                <th id="totalQtyReal">{{ $totalQtyReal ?? 0 }}</th>
              @else
                <th></th>
              @endif
            @else
              @if($sell->status_id != 2)
                <th id="totalQtyReal">{{ $totalQtyReal ?? 0 }}</th>
              @endif
            @endif

            @if($sell->sell_order_type_id == 1)
              @if($sell->status_id != 2)
                <th colspan="3"></th>
              @else
                <th colspan="2"></th>
              @endif
            @elseif($sell->sell_order_type_id == 2)
              @if($sell->status_id == 1)
                <th colspan="3"></th>
              @elseif($sell->status_id == 2)
                <th></th>
              @elseif($sell->status_id == 3)
                <th colspan="2"></th>
              @endif
            @elseif(in_array($sell->sell_order_type_id, [3, 4]))
              <th colspan="2"></th>
            @endif
          @elseif($sell->sell_order_type_id == 5)
            <th>{{ $totalFirst }}</th>
            <th>{{ $totalSold }}</th>
            <th>{{ $totalUpdate }}</th>
            <th></th>
            <th>{{ 'Rp '.number_format($totalPriceAtOrder, 2) }}</th>
          @endif
        @endif
        @if(!isset($sell))
          <th id="totalPrice">Rp 0,00</th>
        @else
          <th id="totalPrice">{{ 'Rp '.number_format($sell->total_price, 2) }}</th>
        @endif
      </tr>
    </tfoot>
</table>
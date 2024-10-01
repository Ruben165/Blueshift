<table class="table table-hover" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="25">No</th>
        <th scope="col" width="150">Barcode Id</th>
        <th scope="col" width="300">Nama Item</th>
        <th scope="col" width="100">Kuantitas Retur</th>
        <th scope="col" width="100">Kuantitas Aktual</th>
        <th scope="col" width="150">Batch-Exp</th>
        <th scope="col" width="150">Harga Barang @if(isset($sell)) Saat Pemesanan @endif</th>
        <th scope="col" width="150">Jumlah</th>
        @if(!isset($sell))
        <th scope="col" width="150">Action</th>
        @endif
      </tr>
    </thead>
    <tbody>
        @php
          $totalQtyRequest = 0;
          $totalQtyReal = 0;
        @endphp
        @if(isset($sell))
          @foreach($sell->partnerItems as $index => $item)
            @php
              $totalQtyReal += $item->pivot->quantity_left + $item->pivot->quantity;
                $qtyRequest = 0;

                foreach($sell->partnerItems as $partnerItem){
                  if($partnerItem->id == $item->id){
                    $qtyRequest = $partnerItem->pivot->quantity;
                    $totalQtyRequest += $qtyRequest;
                    break;
                  }
              }
            @endphp
            <tr>
              <td>{{ (int) $index + 1 }}</td>
              <td>{{ $item->barcode_id }}</td>
              <td>{{ $item->item->name .' ('.$item->item->content.') ('.$item->item->packaging.') ('.$item->item->manufacturer.').' }}</td>
              <td>
                <div name="qtyRequest" id="qtyRequest-{{ $item->barcode_id }}" class="qtyRequest">
                  {{ $qtyRequest }}
                </div>
              </td>
              <td>{{ $item->pivot->quantity_left + $item->pivot->quantity }}</td>
              <td>{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
              <td>{{ 'Rp '.number_format($item->item->price, 2)  }}</td>
              <th id="jumlah-{{ $item->barcode_id }}">{{ !isset($sell) ? 'Rp 0.00' : 'Rp' . number_format($item->item->price * $qtyRequest, 2)  }}</th>
              @if(!isset($route) || $route != 'sell.detail')
              <td>
                <div class="btn btn-danger btn-sm removeButton">
                  <div style="display:flex; align-items:center;">
                    <span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span>
                  </div>
                </div>
              </td>
              @endif
            </tr>
            @endforeach
          @endif
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3">Total</th>
        <th id="totalQtyRequest">{{ $totalQtyRequest ?? 0 }}</th>
        <th id="totalQtyReal">{{ $totalQtyReal ?? 0 }}</th>
        <th colspan="2"></th>
        @if(!isset($sell))
          <th id="totalPrice">Rp 0,00</th>
        @else
          <th id="totalPrice">{{ 'Rp '.number_format($sell->total_price, 2) }}</th>
        @endif
      </tr>
    </tfoot>
</table>
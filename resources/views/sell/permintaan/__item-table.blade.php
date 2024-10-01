<table class="table table-hover" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="25">No</th>
        <th scope="col" width="300">Nama Item</th>
        <th scope="col" width="150">SKU</th>
        <th scope="col" width="100">Kuantitas Request</th>
        <th scope="col" width="100">Kuantitas Kirim</th>
        @if($route != 'sell.permintaan.show')
        <th scope="col" width="150">Action</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @if(isset($consignmentRequest))
        @php
          $totalReq = 0;
          $totalSend = 0;
        @endphp
        @foreach($consignmentRequest->items as $index => $item)
          @php
            $totalReq += $item->pivot->quantity;
            $totalSend += $item->pivot->quantity_send;
          @endphp
          <tr>
            <td>{{ $index+1 }}</td>
            <td>{{ $item->name .' ('.$item->content.') ('.$item->packaging.') ('.$item->manufacturer.').' }}</td>
            @if($route != 'sell.permintaan.show')
            <td>
              <div class="form-group">
                <input type="number" class="form-control itemQuantity" itemId="{{ $item->id }}" min="1" value="{{ $item->pivot->quantity }}" class="form-control">
              </div>
            </td>
            @else
            <td>{{ $item->sku }}</td>
            <td>{{ $item->pivot->quantity }}</td>
            <td>{{ $item->pivot->quantity_send }}</td>
            @endif
            @if($route != 'sell.permintaan.show')
              <td>
                <div class="btn btn-danger btn-sm removeButton">
                  <div style="display:flex; align-items:center;">
                    <span class="fas fa-fw fa-trash"></span><span style="margin-left: 0.25em">Hapus</span>
                  </div>
                </div>
                <div class="btn btn-success btn-sm viewStockItem" sku="{{$item->sku}}">
                  <div style="display:flex; align-items:center;">
                    <span class="fas fa-fw fa-search"></span><span style="margin-left: 0.25em">Lihat Stok</span>
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
        <th colspan="3">
          Total
        </th>
        <th id="totalReq">
          {{ @$totalReq ?? 0 }}
        </th>
        <th id="totalSend">
          {{ @$totalSend ?? 0 }}
        </th>
        @if(!@$route || @$route != 'sell.permintaan.show')
        <th>
        </th>
        @endif
      </tr>
    </tfoot>
</table>
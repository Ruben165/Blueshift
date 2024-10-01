<table class="table" id='dataTable'>
    <thead>
      <tr>
        <th scope="col" width="25">No</th>
        <th scope="col" width="300">Nama Item</th>
        <th scope="col" width="100">Kuantitas Request</th>
        <th scope="col" width="100">Kuantitas Diterima</th>
        <th scope="col" width="150">Harga Barang</th>
        <th scope="col" width="150">Barcode Id</th>
        <th scope="col" width="100">Rak</th>
        <th scope="col" width="150">Batch-Exp</th>
        <th scope="col" width="150">Jumlah Diterima</th>
      </tr>
    </thead>
    <tbody>
        @foreach($buy->items as $indexItem => $item)
          <tr>
            <td>{{ $indexItem + 1 }}</td>
            <td>{{ $item->name . ' (' . $item->content . ') (' . $item->packaging . ').' }}</td>
            <td>{{ $item->pivot->quantity }}</td>
            <td>{{ $item->pivot->arrived_quantity ?? 0 }}</td>
            <td>{{ 'Rp '.number_format($item->pivot->total / $item->pivot->quantity, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ 'Rp '.number_format(($item->pivot->total / $item->pivot->quantity) * ($item->pivot->arrived_quantity ?? 0), 2) }}</td>
          </tr>
          @foreach($buy->partnerItems as $index => $partnerItem)
            @if($partnerItem->item_id == $item->id)
              <tr style="background-color: white">
                <td></td>
                <td></td>
                <td></td>
                <td>
                  {{ $partnerItem->pivot->quantity }}
                </td>
                <td>
                  {{ 'Rp '.number_format($partnerItem->pivot->total / $partnerItem->pivot->quantity, 2) }}
                </td>
                <td>
                  {{ $partnerItem->barcode_id }}
                </td>
                <td>
                  {{ $partnerItem->shelf->name }}
                </td>
                <td>
                  {{ $partnerItem->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $partnerItem->exp_date)->format('m/y') }}
                </td>
                <td>
                  {{ 'Rp '.number_format($partnerItem->pivot->total, 2) }}
                </td>
              </tr>
            @endif
          @endforeach
        @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="8">
          Total Harga Sebelum Diskon dan PPN
        </th>
        <th>
          {{ 'Rp '.number_format($totalPriceAccepted, 2) }}
        </th>
      </tr>
      <tr>
        <th colspan=6 style="vertical-align: middle;">
          Besar Diskon (%):
        </th>
        <th>
          <input type="number" min="0" max="100" value={{ $buy->discount }} disabled class="form-control" id="discount" name="discount" placeholder="Masukkan persenan...">
        </th>
        <th style="vertical-align: middle;">
          Potongan:
        </th>
        <th style="vertical-align: middle;" id="percentageValue">
          {{ $buy->discount ? 'Rp '.number_format($totalPriceAccepted * $buy->discount / 100, 2) : 'Rp 0.00' }}
        </th>
      </tr>
      <tr>
        <th colspan=6 style="vertical-align: middle;">
          Besar PPN (%):
        </th>
        <th>
          <input type="number" min="0" max="100" value={{ $buy->ppn }} disabled class="form-control" id="ppn" name="ppn" placeholder="Masukkan PPN...">
        </th>
        <th style="vertical-align: middle;">
          Nilai PPN:
        </th>
        <th style="vertical-align: middle;" id="ppnValue">
          {{ $buy->ppn ? 'Rp '.number_format(($totalPriceAccepted - ($totalPriceAccepted * $buy->ppn / 100)) * $buy->ppn/100, 2) : 'Rp '.number_format($totalPriceAccepted * 11 / 100, 2) }}
        </th>
      </tr>
      <tr>
        <th colspan="8">Total Harga</th>
        <th id="totalPrice">{{ 'Rp '.number_format($totalPriceAccepted - ($totalPriceAccepted * $buy->discount / 100) + ($totalPriceAccepted - ($totalPriceAccepted * $buy->ppn / 100)) * $buy->ppn/100) }}</th>
      </tr>
    </tfoot>
</table>
<div style="overflow-x: auto;">
  <table class="table table-hover" id='dataTable'>
      <thead style="white-space:nowrap">
        <tr>
          <th>No</th>
          @if(isset($buy) && $buy->status_id == 2)
          <th>Barcode ID</th>
          @endif
          <th>Nama Item &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Qty Pesan</th>
          <th>ID CR/PO/BR &nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Klinik &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Qty Datang</th>
          <th>No Faktur &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Batch &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>ED</th>
          <th>Rak &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>HNA Satuan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Diskon (%) &nbsp;&nbsp;</th>
          <th>Harga Beli &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Jumlah &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          <th>Note &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
          @if(!isset($route))
          <th>Aksi</th>
          @endif
        </tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
        @if(isset($buy) && $buy->status_id == 2)
          <th colspan="3">Total</th>
        @else
          <th colspan="2">
            Total  
          </th>
        @endif
        <th id="totalQtyRequest">
          0
        </th>
        <th colspan="2"></th>
        <th id="totalQtyCame">
          0
        </th>
        <th colspan="4"></th>
        <th id="totalHNA">
          {{-- Rp 0,00 --}}
        </th>
        <th></th>
        <th id="totalBuyPrice">
          Rp 0,00
        </th>
        <th id="totalAmount">
          Rp 0,00
        </th>
        <th colspan="2"></th>
      </tfoot>
  </table>
</div>
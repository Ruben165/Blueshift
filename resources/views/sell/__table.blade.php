<div style="overflow-x: auto;">
    <table class="table table-bordered table-hover" id="sell-list">
        <thead style="white-space:nowrap">
            <tr>
                <th>No</th>
                @if($type == 'Reguler' || $type == 'Konsinyasi')
                    <th>ID Pengiriman</th>
                @elseif($type == 'SO')
                    <th>ID SO</th>
                @else
                    <th>ID Retur</th>
                @endif

                @if($type == 'Transfer' || $type == 'Retur')
                    <th>Klinik Sumber</th>
                @endif

                @if($type != 'Retur')
                    <th>Klinik Tujuan &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                @endif

                @if($type == 'Konsinyasi')
                    <th>Batch Klinik Tujuan</th>
                @endif
                
                @if($type == 'Retur')
                    <th>PIC Retur</th>
                @elseif($type != 'Reguler' && $type != 'Konsinyasi')
                    <th>Nomor Document</th>
                @endif
                
                @if($type != 'Retur')
                    <th>Tanggal Pemesanan</th>
                    <th>Harga Total</th>
                    <th>Tanggal Pengiriman</th>
                @else
                    <th>Tanggal Retur</th>
                    <th>Qty Retur</th>
                    <th>Harga Total</th>
                @endif

                @if($type == 'Konsinyasi')
                    <th>Jadwal SO</th>
                @endif
                
                @if($type == 'SO' || $type == 'Reguler')
                    <th>Status Invoice</th>
                @endif
                
                <th>Status &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                <th width="80">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot style="display: table-header-group !important;">
            <tr>
                <th></th>
                <th></th>
                @if($type != 'Reguler' && $type != 'Konsinyasi')
                <th></th>
                @endif
                <th></th>
                @if($type == 'Konsinyasi')
                <th></th>
                @endif
                <th></th>
                @if($type != 'Reguler' && $type != 'Konsinyasi' && $type != 'SO')
                <th></th>
                @endif
                <th></th>
                @if($type != 'Retur')
                <th></th>
                @endif
                <th></th>
                @if($type == 'Konsinyasi')
                <th></th>
                @endif
                @if($type == 'SO' || $type == 'Reguler')
                <th></th>
                @endif
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

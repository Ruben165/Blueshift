<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#biodata">Biodata</a>
    </li>
    @if($mitra->id != 1)
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#reguler">Reguler + SO</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#konsinyasi">Konsinyasi</a>
    </li>
    @endif
</ul>

<div class="tab-content">
    <div id="biodata" class="card tab-pane fade show active">
      <div class="card-body">
        <div class="form-group">
            <label for="name">Nama Klinik</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama mitra..." value="{{ $mitra->name }}" disabled>
        </div>
        <div class="form-group">
            <label for="phone">No. Telephone Klinik</label>
            <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan telephone mitra..." value="{{ $mitra->phone }}" disabled>
        </div>
        <div class="form-group">
            <label for="address">Alamat Klinik</label>
            <textarea class="form-control" name="address" id="address" cols="30" rows="2" placeholder="Masukkan alamat mitra..." disabled>{{ $mitra->address }}</textarea>
        </div>
        <div class="form-group">
            <label for="sales_name">Nama Sales</label>
            <input type="text" class="form-control" id="sales_name" name="sales_name" placeholder="Masukkan nama sales..." value="{{ $mitra->sales_name }}" disabled>
        </div>
      </div>
    </div>
    @if($mitra->id != 1)
    <div id="reguler" class="card tab-pane fade">
      <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-hover" id="partners-list">
                <thead style="white-space:nowrap">
                    <tr>
                        <th>No</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Nomor Document</th>
                        <th>Harga Total</th>
                        <th>Tanggal Pemesanan</th>
                        <th>Tanggal Sampai</th>
                    </tr>
                </thead>
                <tbody>
                    @if($regulars->count() == 0)
                      <tr><td colspan="6">Data Tidak Tersedia</td></tr>
                    @else
                    @foreach($regulars as $index => $regular)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $regular->sell_order_type_id }}</td>
                            <td>{{ $regular->status_id }}</td>
                            <td>{{ $regular->document_number }}</td>
                            <td>{{ $regular->total_price }}</td>
                            <td>{{ $regular->created_date }}</td>
                            <td>{{ $regular->delivered_at }}</td>
                        </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
      </div>
    </div>
    <div id="konsinyasi" class="card tab-pane fade">
      <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-hover" id="partners-list">
                <thead style="white-space:nowrap">
                    <tr>
                        <th>No</th>
                        <th>Status</th>
                        <th>No. Surat Jalan</th>
                        <th>Harga Total</th>
                        <th>Tanggal Pemesanan</th>
                        <th>Tanggal Sampai</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @if($consignes->count() == 0)
                      <tr><td colspan="6">Data Tidak Tersedia</td></tr>
                    @else
                    @foreach($consignes as $index => $consigne)
                        <tr>
                            <td>{{ $index + 1 }}</td>                            
                            <td>{{ $consigne->status_id }}</td>
                            <td>{{ $consigne->document_number }}</td>
                            <td>{{ $consigne->total_price }}</td>
                            <td>{{ $consigne->created_date }}</td>
                            <td>{{ $consigne->delivered_at }}</td>
                            <td>{{ $consigne->due_at }}</td>
                        </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
      </div>
    </div>
    @endif
</div>
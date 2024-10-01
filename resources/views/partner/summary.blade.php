<div class="card">
    <div class="card-body">
        <form action="{{ route('mitra.changeLogo', ['mitra' => $mitra->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
            <div class="form-group">
                <label for="logo" id="logoLabel">
                    <img src="{{ $mitra->logo == 'images/clinicLogo/default.png' ? asset($mitra->logo) : asset('storage/' . $mitra->logo) }}" alt="Logo {{ $mitra->name }}" id="logoImage">
                </label>
                <input type="file" class="custom-file-input" id="logo" name="logo" onchange="this.form.submit()" hidden>
            </div>
        </form>
        <table class="table">
            <tbody>
              <tr>
                <td>Total Transaksi Regular + SO</td>
                <td>{{ $regulars->count() }}</td>
              </tr>
              <tr>
                <td>Total Transaksi Konsinyasi</td>
                <td>{{ $consignes->count() }}</td>
              </tr>
              <tr>
                <td colspan="2">Status: <span style="font-weight:600;">{{ $mitra->allow_consign == 1 ? 'Konsinyasi' : 'Reguler' }}</span></td>
              </tr>
            </tbody>
        </table>
    </div>
</div>
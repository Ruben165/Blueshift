<div class="card">
    <div class="card-header bg-primary">
        <h5 style="margin-bottom:0;">ID Klinik: {{ $mitra->clinic_id }}</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <tbody>
              <tr>
                <td>
                  <p style="margin-bottom:0;">Batch Mitra:</p>
                  <p style="font-weight:400;margin-bottom:0;">{{ $mitra->groups->count() > 0 ? $mitra->groups->pluck('name')->implode(', ') : '-' }}</p>
                </td>
              </tr>
              <tr>
                <td>
                  <p style="margin-bottom:0;">Wilayah Mitra:</p>
                  <p style="font-weight:400;margin-bottom:0;">{{ $mitra->zones->count() > 0 ? $mitra->zones->pluck('name')->implode(', ') : '-' }}</p>
                </td>
              </tr>
              <tr>
                <td>
                  <p style="margin-bottom:0;"><span class="fas fa-fw fa-share"></span>Email:</p>
                  <a href="mailto:{{ $mitra->email }}">
                    <p style="font-weight:400;margin-bottom:0;">{{ $mitra->email }}</p>
                  </a>
                </td>
              </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <h5 style="margin-bottom:0;">Tambah Mitra</h5>
    </div>
    <form action="{{ route('batch-mitra.partner.store', ['batch_mitra' => $batch_mitra->id]) }}" method="post">
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="clinic_id">Masukkan Nama/ID Mitra</label>
            <select id="clinic_id" class="form-control select2-bootstrap4" name="clinic_id[]" multiple="multiple">
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
    </form>
</div>
<div class="card card-primary">
    <div class="card-header">
        <h5 style="margin-bottom:0;">Tambah Batch Baru</h5>
    </div>
    <form action="{{ route('batch-mitra.store') }}" method="post">
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="name">Nama Batch</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama batch..." value="{{ old('name') }}">
            @if($errors->first('name'))
                <p class="text-danger">
                    {{ $errors->first('name') }}
                </p>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
    </form>
</div>
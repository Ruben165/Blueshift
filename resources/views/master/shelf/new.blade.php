<div class="card">
    <div class="card-header">
        <h4>Tambah Rak Baru</h4>
    </div>
    <form action="{{ route('rak.store') }}" method="post">
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="name">Nama Rak</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama rak..." value="{{ old('name') }}">
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
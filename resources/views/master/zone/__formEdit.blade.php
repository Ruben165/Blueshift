<div class="card card-primary">
    <div class="card-header">
        <h5 style="margin-bottom:0;">Edit Nama Wilayah</h5>
    </div>
    <form action="{{ route('wilayah.update', ['wilayah' => $wilayah->id]) }}" method="post">
    @method('PATCH')
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="name">Nama Wilayah</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama wilayah..." value="{{ old('name') ?? $wilayah->name }}">
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
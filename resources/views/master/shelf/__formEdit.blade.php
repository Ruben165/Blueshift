<div class="card card-primary">
    <div class="card-header">
        <h5 style="margin-bottom:0;">Edit Nama Rak</h5>
    </div>
    <form action="{{ route('rak.update', ['rak' => $rak->id]) }}" method="post">
    @method('PATCH')
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="name">Nama Rak</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama rak..." value="{{ old('name') ?? $rak->name }}">
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
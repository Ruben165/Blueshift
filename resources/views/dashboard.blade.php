@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    @isset($error)
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
            {{ $error }}
        </div>
    @endisset
    @isset($success)
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
            {{ $success }}
        </div>
    @endisset
    <h1>
        Dashboard
    </h1>
@endsection

@section('content')
        <form action="{{ route('dashboard') }}" id="theForm">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="date" name="filterStart" id="filterStart" class="form-control" value="{{ $filterStart ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input type="date" name="filterEnd" id="filterEnd" class="form-control" value="{{ $filterEnd ?? '' }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3>Nilai Pembelian</h3>
                        <h5 class="card-subtitle mb-2 text-muted">{{ $filterStart == '' && $filterEnd == '' ? 'All Time' : ($filterStart == '' && $filterEnd != '' ? 'Sebelum ' . $filterEndName : ($filterStart != '' && $filterEnd == '' ? 'Setelah ' . $filterStartName : 'Antara ' . $filterStartName . ' dan ' . $filterEndName)) }}</h5>
                        <h3 class="text-primary">{{ 'Rp'.number_format($totalBuy, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3>Nilai Penjualan</h3>
                        <h5 class="card-subtitle mb-2 text-muted">{{ $filterStart == '' && $filterEnd == '' ? 'All Time' : ($filterStart == '' && $filterEnd != '' ? 'Sebelum ' . $filterEndName : ($filterStart != '' && $filterEnd == '' ? 'Setelah ' . $filterStartName : 'Antara ' . $filterStartName . ' dan ' . $filterEndName)) }}</h5>
                        <h3 class="text-primary">{{ 'Rp'.number_format($totalSell, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3>Saldo Aset Pusat</h3>
                        <h5 class="card-subtitle mb-2 text-muted">All Time</h5>
                        <h3 class="text-primary">{{ 'Rp'.number_format($totalAssets['pusat'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3>Saldo Aset Mitra</h3>
                        <h5 class="card-subtitle mb-2 text-muted">All Time</h5>
                        <h3 class="text-primary">{{ 'Rp'.number_format($totalAssets['mitra'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 mb-2">
                <h3 style="display: inline;">Reminder Konsinyasi</h3>
                <a href="{{ route('sell.index', ['type' => 'Konsinyasi']) }}" class="btn-primary btn-sm ml-2">Go to Menu</a>
            </div>
            <div class="col-md-4 mb-2">
                <h3 style="display: inline;">Notes</h3>
                <a id="addNote" href="#" class="btn-primary btn-sm ml-2">Add</a>
            </div>
            <div class="col-md-8">
                <div style="overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="sell-list">
                        <thead style="white-space:nowrap">
                            <tr>
                                <th>No</th>
                                <th>Klinik Tujuan</th>
                                <th>Nomor Surat Jalan FIRST</th>
                                <th>Jadwal SO Konsinyasi</th>
                                <th>Day Until Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($konsinyasiDues as $index => $sellOrder)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $sellOrder->destinationPartner->clinic_id . ' - ' . $sellOrder->destinationPartner->name }}</td>
                                <td>{{ $sellOrder->document_number }}</td>
                                <td>{{ Carbon\Carbon::parse($sellOrder->due_at)->format('d-m-Y') }}</td>
                                <td>{{ $sellOrder->diff }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>                
            </div>
            <div class="col-md-4">
                <table class="table table-bordered table-hover" id="note-list">
                    <tbody style="cursor: pointer;">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3 class="mb-2">Rolldown</h3>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Top 5 Klinik Konsinyasi</h4>
                        <h5 class="card-subtitle mb-2 text-muted">{{ $filterStart == '' && $filterEnd == '' ? 'All Time' : ($filterStart == '' && $filterEnd != '' ? 'Sebelum ' . $filterEndName : ($filterStart != '' && $filterEnd == '' ? 'Setelah ' . $filterStartName : 'Antara ' . $filterStartName . ' dan ' . $filterEndName)) }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @if(count($top5Konsinyasi) > 0)
                            @foreach($top5Konsinyasi as $top5)
                            <li class="list-group-item">{{ $top5->clinic_id . ' - ' . $top5->name }}</li>
                            @endforeach
                            @else
                            <p>Data Tidak Tersedia</p>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Top 5 Klinik Regular</h4>
                        <h5 class="card-subtitle mb-2 text-muted">{{ $filterStart == '' && $filterEnd == '' ? 'All Time' : ($filterStart == '' && $filterEnd != '' ? 'Sebelum ' . $filterEndName : ($filterStart != '' && $filterEnd == '' ? 'Setelah ' . $filterStartName : 'Antara ' . $filterStartName . ' dan ' . $filterEndName)) }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @if(count($top5Regular) > 0)
                            @foreach($top5Regular as $top5)
                            <li class="list-group-item">{{ $top5->clinic_id . ' - ' . $top5->name }}</li>
                            @endforeach
                            @else
                            <p>Data Tidak Tersedia</p>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Top 5 Penjualan Klinik Tertinggi</h4>
                        <h5 class="card-subtitle mb-2 text-muted">{{ $filterStart == '' && $filterEnd == '' ? 'All Time' : ($filterStart == '' && $filterEnd != '' ? 'Sebelum ' . $filterEndName : ($filterStart != '' && $filterEnd == '' ? 'Setelah ' . $filterStartName : 'Antara ' . $filterStartName . ' dan ' . $filterEndName)) }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @if(count($top5PenjualanTertinggi) > 0)
                            @foreach($top5PenjualanTertinggi as $top5)
                            <li class="list-group-item">{{ $top5->clinic_id . ' - ' . $top5->name }}</li>
                            @endforeach
                            @else
                            <p>Data Tidak Tersedia</p>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Top 20 Obat Fast Moving</h4>
                        <h5 class="card-subtitle mb-2 text-muted">{{ $filterStart == '' && $filterEnd == '' ? 'All Time' : ($filterStart == '' && $filterEnd != '' ? 'Sebelum ' . $filterEndName : ($filterStart != '' && $filterEnd == '' ? 'Setelah ' . $filterStartName : 'Antara ' . $filterStartName . ' dan ' . $filterEndName)) }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @if(count($top20FastMoving) > 0)
                            @foreach($top20FastMoving as $top20)
                            <li class="list-group-item">{{ $top20->sku . ' - ' . $top20->name }}</li>
                            @endforeach
                            @else
                            <p>Data Tidak Tersedia</p>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal hide fade" tabindex="-1" id="modalAddNote">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Add Note</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form action="{{ route('note.store') }}" method="post">
                @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <textarea class="form-control" name="noteDescription" id="noteDescription" cols="30" rows="10" required placeholder="Masukkan notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Tambah Note</button>
                    </div>
                </form>
              </div>
            </div>
        </div>

        <div class="modal hide fade" tabindex="-1" id="modalEditNote">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('note.update') }}" method="post">
            @csrf
                <input type="hidden" name="id" id="noteIdForm">
                <div class="modal-body">
                    <div class="form-group">
                        <textarea class="form-control" name="noteDescription" id="noteDescriptionEdit" cols="30" rows="10" required placeholder="Masukkan notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="deleteNote">Hapus Note</button>
                    <button type="submit" class="btn btn-primary">Edit Note</button>
                </div>
            </form>

            <form action="{{ route('note.destroy') }}" id="deleteNoteForm" method="post">
            @csrf
                <input type="hidden" name="id" id="idNoteDestroy">
            </form>
            </div>
        </div>
        </div>
@endsection

@section('extra-css')
    <style scoped>
        .content_header{
            display: flex;
            justify-content: space-between;
        }
    </style>
@endsection

@section('extra-js')
    <script type="text/javascript">

        $(document).ready(function() {
            function initNoteDatatable(){
                let table = $('#note-list').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": "{{ route($route) }}",
                    "columnDefs": [
                        {
                            "data": 'show',
                            "targets": 0,
                            "render": function(data, type, row, meta) {
                                let content = ''

                                if (data !== '') {
                                    data.map((note, idx) => {
                                        content += `<span class="noteEdit" noteDescription="` + note.description + `" noteId="` + note.id + `">` + note.description + `</span>`;
                                    })

                                    return content;
                                } else {
                                    return '';
                                }
                            },
                            "orderable": false
                        }
                    ],
                    lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]], 
                    order: [],
                    searching: false,
                    search: {
                        smart: false,
                        "caseInsensitive": false
                    }
                })
            }

            initNoteDatatable()
        });

        $('#addNote').on('click', function(e) {
            e.preventDefault();
            
            $('#modalAddNote').modal({
                show: true
            })
        })

        $('#note-list tbody').on('click', 'tr', function(e) {
            e.preventDefault();

            let spanValue = $(this).find('span');
            
            $('#noteIdForm').val($(spanValue).attr('noteId'))
            $('#noteDescriptionEdit').val($(spanValue).attr('noteDescription'))

            $('#modalEditNote').modal({
                show: true
            })
        })

        $('#deleteNote').on('click', function(e) {
            e.preventDefault();

            $('#idNoteDestroy').val($('#noteIdForm').val())

            $('#deleteNoteForm').submit()
        })
    </script>
@endsection
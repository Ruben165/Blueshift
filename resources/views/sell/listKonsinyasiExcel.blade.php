<table>
    <thead>
        <tr>
            <th colspan="4"></th>
            <th style="font-size: 7pt; font-weight: bold;" colspan="3">STOK OPNAME</th>
        </tr>
        <tr>
            <th colspan="4"></th>
            <th style="font-size: 7pt;">Tanggal</th>
            <th style="font-size: 7pt;" colspan="3">: {{ $formated_date }}</th>
        </tr>
        <tr>
            <th colspan="4"></th>
            <th style="font-size: 7pt;">Nama Klinik</th>
            <th style="font-size: 7pt;" colspan="3">: {{ $partner->name }}</th>
        </tr>
        <tr>
            <th colspan="4"></th>
            <th style="font-size: 7pt;">PIC Checker</th>
            <th style="font-size: 7pt;" colspan="3">:</th>
        </tr>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">ID Database</th>
            <th style="width:30%; font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Nama Barang</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Satuan</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Batch-Exp</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">GOL</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Qty Awal</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Qty Update</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Terjual</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Verif 1</th>
            <th style="font-weight: bold; font-size: 7pt; background-color: #4472c4; color: white;">Verif 2</th>
        </tr>
    </thead>
    <tbody>
        @foreach($partnerItems as $item)
        <tr>
            <td style="font-size: 7pt;">{{ $item->barcode_id }}</td>
            <td style="font-size: 7pt;">{{ strtoupper($item->item->name . ' ' . getBerat($item->item->packaging) . ' (' . $item->item->manufacturer . ')') }}</td>
            <td style="text-align: center; font-size: 7pt;">{{ strtoupper($item->item->unit) }}</td>
            <td style="font-size: 7pt;">{{ $item->batch . '-' . Carbon\Carbon::createFromFormat('Y-m-d', $item->exp_date)->format('m/y') }}</td>
            <td style="text-align: center; font-size: 7pt;">{{ strtoupper($item->item->type->name) }}</td>
            <td style="text-align: center; font-size: 7pt;">{{ !isset($isHasilSO) || $isHasilSO == false ? $item->stock_qty : ($item->pivot->quantity + $item->pivot->quantity_left) }}</td>
            <td style="text-align: center; font-size: 7pt;">{{ !isset($isHasilSO) || $isHasilSO == false ? '' : $item->pivot->quantity_left }}</td>
            <td style="text-align: center; font-size: 7pt;">{{ !isset($isHasilSO) || $isHasilSO == false ? '' : $item->pivot->quantity }}</td>
            <td style="font-size: 7pt;"></td>
            <td style="font-size: 7pt;"></td>
        </tr>
        @endforeach
    </tbody>
</table>

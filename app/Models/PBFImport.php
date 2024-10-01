<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PBFImport extends Model
{
    use HasFactory;

    protected $table = 'pbf_temp';

    protected $fillable = ['sku', 'qtyRequest', 'idCRPOBR', 'clinic', 'qtyCame', 'faktur', 'batch', 'expired', 'shelf', 'HNAEach', 'discount', 'note'];
}
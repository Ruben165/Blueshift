<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOImport extends Model
{
    use HasFactory;

    protected $table = 'so_temp';

    protected $fillable = ['barcode_id', 'quantity', 'partner_id'];
}

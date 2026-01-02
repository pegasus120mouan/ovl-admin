<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePaiement extends Model
{
    protected $table = 'type_paiement';

    public $timestamps = false;

    protected $fillable = [
        'operateur',
        'logo',
    ];
}

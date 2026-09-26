<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectifCommercial extends Model
{
    protected $table = 'objectifs_commerciaux';

    public $timestamps = false;

    protected $fillable = [
        'periode',
        'montant',
    ];

    protected $casts = [
        'periode' => 'date',
        'montant' => 'integer',
    ];
}

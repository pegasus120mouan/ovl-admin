<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartePoint extends Model
{
    protected $fillable = [
        'nom',
        'latitude',
        'longitude',
        'description',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}

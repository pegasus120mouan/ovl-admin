<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutLivraison extends Model
{
    protected $table = 'statut_livraison';

    public $timestamps = false;

    protected $fillable = [
        'statut',
    ];
}

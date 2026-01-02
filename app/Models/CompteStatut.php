<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteStatut extends Model
{
    protected $table = 'compte_statut';

    public $timestamps = false;

    protected $fillable = [
        'statut',
    ];
}

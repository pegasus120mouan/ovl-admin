<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaiePeriode extends Model
{
    protected $table = 'paie_periodes';

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function fiches(): HasMany
    {
        return $this->hasMany(PaieLivreur::class, 'periode_id');
    }
}

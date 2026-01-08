<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrat extends Model
{
    protected $table = 'contrats';

    protected $primaryKey = 'contrat_id';

    public $timestamps = false;

    protected $fillable = [
        'id_engin',
        'vignette_date_debut',
        'vignette_date_fin',
        'assurance_date_debut',
        'assurance_date_fin',
        'date_debut',
        'date_fin',
        'montant',
        'statut',
    ];

    protected $casts = [
        'vignette_date_debut' => 'date',
        'vignette_date_fin' => 'date',
        'assurance_date_debut' => 'date',
        'assurance_date_fin' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant' => 'decimal:2',
    ];

    public function engin(): BelongsTo
    {
        return $this->belongsTo(Engin::class, 'id_engin', 'engin_id');
    }
}

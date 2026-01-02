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
        'date_debut',
        'date_fin',
        'montant',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant' => 'decimal:2',
    ];

    public function engin(): BelongsTo
    {
        return $this->belongsTo(Engin::class, 'id_engin', 'engin_id');
    }
}

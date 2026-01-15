<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaieAjustement extends Model
{
    protected $table = 'paie_ajustements';

    protected $fillable = [
        'paie_livreur_id',
        'livreur_id',
        'periode_id',
        'type',
        'montant',
        'motif',
        'statut',
        'cree_par',
        'valide_par',
        'date_validation',
        'commande_id',
    ];

    protected $casts = [
        'date_validation' => 'date',
    ];

    public function fiche(): BelongsTo
    {
        return $this->belongsTo(PaieLivreur::class, 'paie_livreur_id');
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'livreur_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PaiePeriode::class, 'periode_id');
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }
}

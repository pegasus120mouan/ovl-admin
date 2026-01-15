<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaieLivreur extends Model
{
    protected $table = 'paie_livreurs';

    protected $fillable = [
        'periode_id',
        'livreur_id',
        'salaire_base',
        'total_ajustements',
        'net_a_payer',
        'statut',
        'date_validation',
        'valide_par',
        'date_paiement',
        'montant_paye',
        'reference_paiement',
        'paye_par',
    ];

    protected $casts = [
        'date_validation' => 'date',
        'date_paiement' => 'date',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PaiePeriode::class, 'periode_id');
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'livreur_id');
    }

    public function ajustements(): HasMany
    {
        return $this->hasMany(PaieAjustement::class, 'paie_livreur_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementLivreur extends Model
{
    protected $table = 'paiements_livreurs';

    public const STATUT_EN_ATTENTE = 'En attente';
    public const STATUT_VALIDE = 'Validé';
    public const STATUT_REJETE = 'Rejeté';

    public const MODES = ['OM', 'Wave', 'Moov Money'];

    protected $fillable = [
        'utilisateur_id',
        'date_versement',
        'montant',
        'montant_valide',
        'mode_paiement',
        'reference',
        'statut',
        'motif_rejet',
        'traite_le',
    ];

    protected $casts = [
        'date_versement' => 'date',
        'montant' => 'integer',
        'montant_valide' => 'integer',
        'traite_le' => 'datetime',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}

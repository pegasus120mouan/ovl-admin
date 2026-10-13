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
    // Paiement validé puis annulé par l'admin (le versement du jour a été remis à zéro).
    public const STATUT_ANNULE = 'Annulé';

    protected $fillable = [
        'utilisateur_id',
        'date_versement',
        'montant',
        'montant_valide',
        'type_paiement_id',
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

    /**
     * Opérateur de paiement (table type_paiement : Orange, MTN, Wave, Djamo...).
     */
    public function typePaiement(): BelongsTo
    {
        return $this->belongsTo(TypePaiement::class, 'type_paiement_id');
    }

    /**
     * Nom de l'opérateur : issu de type_paiement, avec repli sur la valeur enregistrée
     * (anciennes déclarations ou opérateur supprimé).
     */
    public function getOperateurAttribute(): string
    {
        return (string) ($this->typePaiement->operateur ?? $this->mode_paiement ?? '');
    }
}

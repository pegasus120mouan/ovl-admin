<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriquePaiementLivreur extends Model
{
    protected $table = 'historique_paiements_livreurs';

    public const TYPE_PAIEMENT = 'paiement';
    public const TYPE_ANNULATION = 'annulation';

    public const ORIGINE_MANUEL = 'manuel';
    public const ORIGINE_MASSE = 'masse';
    public const ORIGINE_APPLICATION = 'application';
    // Paiement antérieur au journal, repris du total versé du jour (points_livreurs).
    public const ORIGINE_REPRISE = 'reprise';

    public const ORIGINES = [
        self::ORIGINE_MANUEL => 'Saisie manuelle',
        self::ORIGINE_MASSE => 'Solde en masse',
        self::ORIGINE_APPLICATION => 'Application livreur',
        self::ORIGINE_REPRISE => 'Historique antérieur',
    ];

    protected $fillable = [
        'utilisateur_id',
        'date_versement',
        'type',
        'origine',
        'montant',
        'paiement_livreur_id',
        'enregistre_par',
    ];

    protected $casts = [
        'date_versement' => 'date',
        'montant' => 'integer',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'enregistre_par');
    }

    public function paiementLivreur(): BelongsTo
    {
        return $this->belongsTo(PaiementLivreur::class, 'paiement_livreur_id');
    }

    public function estAnnulation(): bool
    {
        return $this->type === self::TYPE_ANNULATION;
    }

    public function libelleOrigine(): string
    {
        return self::ORIGINES[$this->origine] ?? (string) $this->origine;
    }
}

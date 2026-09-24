<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BordereauCommission extends Model
{
    protected $table = 'bordereaux_commissions';

    public $timestamps = false;

    protected $fillable = [
        'numero',
        'commercial_id',
        'date_debut',
        'date_fin',
        'genere_le',
        'nb_colis',
        'base_livraison',
        'montant',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'genere_le' => 'datetime',
    ];

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'commercial_id');
    }

    public function commandes(): BelongsToMany
    {
        return $this->belongsToMany(Commande::class, 'bordereau_commission_colis', 'bordereau_id', 'commande_id')
            ->withPivot('montant');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(PaiementCommission::class, 'bordereau_id');
    }

    public function montantPaye(): int
    {
        return (int) $this->paiements->sum('montant');
    }

    public function resteAPayer(): int
    {
        return max(0, (int) $this->montant - $this->montantPaye());
    }
}

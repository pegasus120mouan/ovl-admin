<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementCommission extends Model
{
    protected $table = 'paiements_commissions';

    public $timestamps = false;

    protected $fillable = [
        'commercial_id',
        'bordereau_id',
        'periode',
        'montant',
        'date_paiement',
        'mode',
        'statut',
        'recu',
    ];

    protected $casts = [
        'periode' => 'date',
        'date_paiement' => 'date',
    ];

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'commercial_id');
    }

    public function bordereau(): BelongsTo
    {
        return $this->belongsTo(BordereauCommission::class, 'bordereau_id');
    }
}

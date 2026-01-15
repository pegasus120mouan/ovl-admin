<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $table = 'factures';

    protected $fillable = [
        'numero',
        'client_id',
        'date_facture',
        'date_debut',
        'date_fin',
        'statut',
        'total_ht',
        'total_ttc',
        'tva_taux',
        'tva_montant',
        'remise',
        'note',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'client_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(FactureLigne::class, 'facture_id');
    }
}

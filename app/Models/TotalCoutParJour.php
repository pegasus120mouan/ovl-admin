<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TotalCoutParJour extends Model
{
    protected $table = 'table_total_cout_par_jour';

    public $timestamps = false;

    protected $fillable = [
        'boutique',
        'date_cout',
        'total_cout',
        'type_paiement_id',
        'statut_paiement',
    ];

    protected $casts = [
        'date_cout' => 'date',
        'total_cout' => 'decimal:2',
    ];

    protected $attributes = [
        'statut_paiement' => 'Non Payé',
    ];

    public function typePaiement(): BelongsTo
    {
        return $this->belongsTo(TypePaiement::class, 'type_paiement_id');
    }

    public function scopePaye($query)
    {
        return $query->where('statut_paiement', 'Payé');
    }

    public function scopeNonPaye($query)
    {
        return $query->where('statut_paiement', 'Non Payé');
    }
}

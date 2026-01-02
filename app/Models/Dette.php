<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dette extends Model
{
    protected $table = 'dette';

    public $timestamps = false;

    protected $fillable = [
        'nom_debiteur',
        'montant_initial',
        'montant_actuel',
        'montants_payes',
        'reste',
        'date_dette',
        'date_echeance',
        'statut',
    ];

    protected $casts = [
        'montant_initial' => 'decimal:2',
        'montant_actuel' => 'decimal:2',
        'montants_payes' => 'decimal:2',
        'reste' => 'decimal:2',
        'date_dette' => 'date',
        'date_echeance' => 'date',
    ];

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class, 'dette_id');
    }

    public function scopeEnCours($query)
    {
        return $query->where('reste', '>', 0);
    }

    public function scopeSolde($query)
    {
        return $query->where('reste', '<=', 0);
    }
}

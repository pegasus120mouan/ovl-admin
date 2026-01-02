<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commande extends Model
{
    protected $table = 'commandes';

    public $timestamps = false;

    protected $fillable = [
        'utilisateur_id',
        'livreur_id',
        'communes',
        'cout_global',
        'cout_livraison',
        'cout_reel',
        'statut',
        'date_reception',
        'date_livraison',
        'date_retour',
    ];

    protected $casts = [
        'date_reception' => 'date',
        'date_livraison' => 'date',
        'date_retour' => 'date',
    ];

    protected $attributes = [
        'statut' => 'Non Livré',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'livreur_id');
    }

    public function scopeLivre($query)
    {
        return $query->where('statut', 'Livré');
    }

    public function scopeNonLivre($query)
    {
        return $query->where('statut', 'Non Livré');
    }

    public function scopeRetour($query)
    {
        return $query->where('statut', 'Retour');
    }

    public function scopeParDate($query, $date)
    {
        return $query->whereDate('date_reception', $date);
    }

    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_reception', [$dateDebut, $dateFin]);
    }
}

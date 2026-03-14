<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'utilisateur_id',
        'type_reclamation',
        'montant_actuel',
        'montant_reclame',
        'statut',
        'reponse_admin',
        'date_traitement',
    ];

    protected $casts = [
        'date_traitement' => 'datetime',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function client()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'montant_incorrect' => 'Montant incorrect',
            'commune_incorrecte' => 'Commune incorrecte',
            'statut_incorrect' => 'Statut incorrect',
            'autre' => 'Autre',
        ];

        return $labels[$this->type_reclamation] ?? $this->type_reclamation;
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'en_attente' => 'En attente',
            'acceptee' => 'Acceptée',
            'refusee' => 'Refusée',
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutBadgeClassAttribute()
    {
        $classes = [
            'en_attente' => 'warning',
            'acceptee' => 'success',
            'refusee' => 'danger',
        ];

        return $classes[$this->statut] ?? 'secondary';
    }
}

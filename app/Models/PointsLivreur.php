<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsLivreur extends Model
{
    protected $table = 'points_livreurs';

    public $timestamps = false;

    protected $fillable = [
        'utilisateur_id',
        'recette',
        'depense',
        'gain_jour',
        'date_commande',
    ];

    protected $casts = [
        'date_commande' => 'date',
    ];

    public function livreur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}

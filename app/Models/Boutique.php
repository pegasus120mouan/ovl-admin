<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Boutique extends Model
{
    protected $table = 'boutiques';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'logo',
        'type_articles',
        'statut',
    ];

    protected $attributes = [
        'logo' => 'boutiques/default_boutiques.png',
    ];

    protected $casts = [
        'statut' => 'boolean',
    ];

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class, 'boutique_id');
    }

    public function gerant(): HasOne
    {
        return $this->hasOne(Utilisateur::class, 'boutique_id')->where('role', 'clients');
    }

    public function commandes(): HasManyThrough
    {
        return $this->hasManyThrough(
            Commande::class,
            Utilisateur::class,
            'boutique_id',
            'utilisateur_id'
        );
    }
}

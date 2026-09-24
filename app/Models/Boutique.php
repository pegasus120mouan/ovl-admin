<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'latitude',
        'longitude',
        'commune_id',
        'commercial_id',
    ];

    protected $attributes = [
        'logo' => 'boutiques/default_boutiques.png',
    ];

    protected $casts = [
        'statut' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id', 'commune_id');
    }

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

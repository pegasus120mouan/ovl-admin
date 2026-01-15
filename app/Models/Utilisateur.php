<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Utilisateur extends Model
{
    protected $table = 'utilisateurs';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenoms',
        'contact',
        'login',
        'avatar',
        'password',
        'code_pin',
        'role',
        'boutique_id',
        'statut_compte',
        'salaire_mensuel',
    ];

    protected $hidden = [
        'password',
        'code_pin',
    ];

    protected $casts = [
        'statut_compte' => 'boolean',
    ];

    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class, 'boutique_id');
    }

    public function commandesClient(): HasMany
    {
        return $this->hasMany(Commande::class, 'utilisateur_id');
    }

    public function commandesLivreur(): HasMany
    {
        return $this->hasMany(Commande::class, 'livreur_id');
    }

    public function engins(): HasMany
    {
        return $this->hasMany(Engin::class, 'utilisateur_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'utilisateur_id');
    }

    public function imprevus(): HasMany
    {
        return $this->hasMany(Imprevu::class, 'livreur_id');
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'clients');
    }

    public function scopeLivreurs($query)
    {
        return $query->where('role', 'livreur');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut_compte', 1);
    }
}

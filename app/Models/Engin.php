<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Engin extends Model
{
    protected $table = 'engins';

    protected $primaryKey = 'engin_id';

    public $timestamps = false;

    protected $fillable = [
        'utilisateur_id',
        'marque',
        'modele',
        'immatriculation',
        'type_engin',
        'photo',
    ];

    protected $attributes = [
        'photo' => 'default.jpg',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function typeEngin(): BelongsTo
    {
        return $this->belongsTo(TypeEngin::class, 'type_engin', 'id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'engin_id', 'engin_id');
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class, 'id_engin', 'engin_id');
    }
}

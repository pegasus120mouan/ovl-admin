<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    protected $table = 'communes';

    protected $primaryKey = 'commune_id';

    public $timestamps = false;

    protected $fillable = [
        'nom_commune',
    ];

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'communes_zones', 'commune_id', 'zone_id');
    }

    public function prix(): HasMany
    {
        return $this->hasMany(Prix::class, 'commune_id');
    }
}

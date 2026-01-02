<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $table = 'zones';

    protected $primaryKey = 'zone_id';

    public $timestamps = false;

    protected $fillable = [
        'nom_zone',
    ];

    public function communes(): BelongsToMany
    {
        return $this->belongsToMany(Commune::class, 'communes_zones', 'zone_id', 'commune_id');
    }

    public function prix(): HasMany
    {
        return $this->hasMany(Prix::class, 'zone_id');
    }
}

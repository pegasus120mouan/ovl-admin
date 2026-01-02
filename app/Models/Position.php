<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Position extends Model
{
    protected $table = 'position';

    protected $primaryKey = 'position_id';

    public $timestamps = false;

    protected $fillable = [
        'engin_id',
        'utilisateur_id',
        'latitude',
        'longitude',
        'date_position',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'date_position' => 'datetime',
    ];

    public function engin(): BelongsTo
    {
        return $this->belongsTo(Engin::class, 'engin_id', 'engin_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}

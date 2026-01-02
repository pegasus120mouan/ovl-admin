<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prix extends Model
{
    protected $table = 'prix';

    protected $primaryKey = 'prix_id';

    public $timestamps = false;

    protected $fillable = [
        'commune_id',
        'zone_id',
        'prix',
    ];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id', 'commune_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'zone_id');
    }
}

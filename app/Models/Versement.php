<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Versement extends Model
{
    protected $table = 'versements';

    public $timestamps = false;

    protected $fillable = [
        'dette_id',
        'montant_versement',
        'date_versement',
    ];

    protected $casts = [
        'date_versement' => 'date',
    ];

    public function dette(): BelongsTo
    {
        return $this->belongsTo(Dette::class, 'dette_id');
    }
}

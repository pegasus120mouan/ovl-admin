<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Imprevu extends Model
{
    protected $table = 'imprevu';

    public $timestamps = false;

    protected $fillable = [
        'livreur_id',
        'description',
        'montant',
        'date_imprevu',
    ];

    protected $casts = [
        'date_imprevu' => 'date',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'livreur_id');
    }
}

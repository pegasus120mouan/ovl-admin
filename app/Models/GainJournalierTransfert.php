<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GainJournalierTransfert extends Model
{
    protected $table = 'gain_journalier_transferts';

    public $timestamps = false;

    protected $fillable = [
        'date_gain',
        'transfere_at',
    ];

    protected $casts = [
        'date_gain' => 'date',
        'transfere_at' => 'datetime',
    ];

    public static function datesTransferees(string $dateDebut, string $dateFin): array
    {
        return static::query()
            ->whereDate('date_gain', '>=', $dateDebut)
            ->whereDate('date_gain', '<=', $dateFin)
            ->get()
            ->map(fn ($transfert) => $transfert->date_gain->toDateString())
            ->all();
    }
}

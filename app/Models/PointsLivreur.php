<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsLivreur extends Model
{
    protected $table = 'points_livreurs';

    public $timestamps = false;

    protected $fillable = [
        'utilisateur_id',
        'recette',
        'depense',
        'gain_jour',
        'date_commande',
    ];

    protected $casts = [
        'date_commande' => 'date',
    ];

    public function livreur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function recalculateGain(): void
    {
        $this->gain_jour = (int) ($this->recette ?? 0) - (int) ($this->depense ?? 0);
    }

    public static function forLivreurAndDate(int $utilisateurId, string $date, ?int $excludeId = null): ?self
    {
        $query = static::query()
            ->where('utilisateur_id', $utilisateurId)
            ->whereDate('date_commande', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    public static function consolidateDuplicatesForLivreurDay(int $utilisateurId, string $date): ?self
    {
        $points = static::query()
            ->where('utilisateur_id', $utilisateurId)
            ->whereDate('date_commande', $date)
            ->orderBy('id')
            ->get();

        if ($points->isEmpty()) {
            return null;
        }

        if ($points->count() === 1) {
            return $points->first();
        }

        $keep = $points->first();
        $keep->recette = (int) $points->sum('recette');
        $keep->depense = (int) $points->sum('depense');
        $keep->recalculateGain();
        $keep->save();

        static::query()
            ->where('utilisateur_id', $utilisateurId)
            ->whereDate('date_commande', $date)
            ->where('id', '!=', $keep->id)
            ->delete();

        return $keep->fresh();
    }
}

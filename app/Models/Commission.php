<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table = 'commissions';

    public $timestamps = false;

    protected $fillable = [
        'taux',
    ];

    protected $casts = [
        'taux' => 'decimal:2',
    ];

    public static function globale(): ?self
    {
        return static::query()->first();
    }

    public static function enregistrer(float $taux): self
    {
        $regle = static::globale();

        if ($regle) {
            $regle->update(['taux' => $taux]);

            return $regle->fresh();
        }

        return static::query()->create(['taux' => $taux]);
    }

    public function montantPour(Commande $commande): int
    {
        return $this->montantPourBase((int) $commande->cout_livraison);
    }

    public function montantPourBase(int $baseLivraison): int
    {
        return (int) round($baseLivraison * ((float) $this->taux) / 100);
    }
}

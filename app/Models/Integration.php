<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Integration extends Model
{
    protected $table = 'integrations';

    protected $fillable = [
        'nom',
        'utilisateur_id',
        'identifiant',
        'token_hash',
        'token_hint',
        'actif',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public static function generateIdentifiant(): string
    {
        do {
            $identifiant = 'ovl_' . Str::lower(Str::random(16));
        } while (static::where('identifiant', $identifiant)->exists());

        return $identifiant;
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function setPlainToken(string $plainToken): void
    {
        $this->token_hash = hash('sha256', $plainToken);
        $this->token_hint = substr($plainToken, -4);
    }

    public function matchesToken(string $plainToken): bool
    {
        return hash_equals($this->token_hash, hash('sha256', $plainToken));
    }

    public function apiUrl(): string
    {
        return url('/api/v1/integrations/commandes');
    }
}

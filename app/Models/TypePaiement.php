<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePaiement extends Model
{
    protected $table = 'type_paiement';

    public $timestamps = false;

    protected $fillable = [
        'operateur',
        'logo',
    ];

    /**
     * Dossier public (public/img/type_paiement) où sont déposés les fichiers de logos
     * dont le nom figure dans la colonne `logo`.
     */
    public const LOGO_DIRECTORY = 'img/type_paiement';

    /**
     * URL du logo, ou null si aucun fichier n'est disponible.
     * Accepte une URL complète dans la colonne `logo`, sinon un nom de fichier du dossier public.
     */
    public function getLogoUrlAttribute(): ?string
    {
        $logo = trim((string) ($this->logo ?? ''));
        if ($logo === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $logo)) {
            return $logo;
        }

        // basename() : évite toute remontée de répertoire via la valeur stockée en base.
        $file = basename($logo);
        $relative = self::LOGO_DIRECTORY . '/' . $file;

        return is_file(public_path($relative)) ? asset($relative) : null;
    }
}

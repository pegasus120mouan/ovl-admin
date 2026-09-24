<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class R2ImageStore
{
    /**
     * @var list<string>
     */
    private const DEFAULT_AVATARS = [
        'default.jpg',
        'livreur.png',
        'administrateurs/admins.png',
        'utilisateurs/utilisateurs.png',
        'livreurs/livreur.png',
        'gestionnaires/gestionnaire.png',
    ];

    public function store(UploadedFile $file, string $folder, string $field = 'avatar'): string
    {
        try {
            $path = $file->store($folder, 'r2');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => "Impossible d'envoyer le fichier vers Cloudflare (ovl-delivery/{$folder}). Vérifiez les clés API R2.",
            ]);
        }

        if (! is_string($path) || ! str_starts_with($path, $folder.'/')) {
            throw ValidationException::withMessages([
                $field => "L'enregistrement vers Cloudflare R2 a échoué.",
            ]);
        }

        return $path;
    }

    public function deleteCustom(?string $key): void
    {
        if (! $key || in_array($key, self::DEFAULT_AVATARS, true)) {
            return;
        }

        try {
            Storage::disk('r2')->delete($key);
        } catch (\Throwable) {
            //
        }
    }
}

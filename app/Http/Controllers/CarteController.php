<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Carte;
use App\Models\CartePoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CarteController extends Controller
{
    public function index(Request $request): View
    {
        $vue = $this->requestedVue($request);

        $regions = Carte::ofType(Carte::TYPE_REGIONS);
        $departements = Carte::ofType(Carte::TYPE_DEPARTEMENTS);
        $pointsCarte = Carte::ofType(Carte::TYPE_POINTS);
        $points = CartePoint::query()->orderBy('nom')->get();

        $carteActive = match ($vue) {
            Carte::TYPE_DEPARTEMENTS => $departements,
            Carte::TYPE_POINTS => $pointsCarte,
            default => $regions,
        };

        $zones = Carte::uniqueFeatureNames($regions?->geojson ?? $carteActive?->geojson ?? []);
        $zone = $this->requestedZone($request, $zones);
        $geojson = $carteActive?->geojson ?? ['type' => 'FeatureCollection', 'features' => []];

        if ($zone !== '') {
            $geojson = Carte::filterByZone($geojson, $zone, $regions?->geojson);
        }

        $zoneFeatures = $geojson['features'] ?? [];

        if ($zone !== '' && $zoneFeatures === []) {
            $zoneFeatures = Carte::filterByZone(
                $regions?->geojson ?? ['type' => 'FeatureCollection', 'features' => []],
                $zone
            )['features'] ?? [];
        }

        if ($vue === Carte::TYPE_POINTS && $zone !== '') {
            $points = $points->filter(fn (CartePoint $point) => Carte::containsLatLng(
                $zoneFeatures,
                (float) $point->latitude,
                (float) $point->longitude
            ))->values();
        }

        $boutiques = $this->boutiquesSurCarte($zoneFeatures, $zone !== '');

        $titreCarte = $zone !== ''
            ? $this->titreZone($zone)
            : match ($vue) {
                Carte::TYPE_DEPARTEMENTS => 'Carte des départements importés',
                Carte::TYPE_POINTS => 'Localisation des points',
                default => 'Carte des régions importées',
            };

        $traces = Carte::featureCount($geojson);

        if ($vue === Carte::TYPE_POINTS) {
            $traces += $points->count();
        }

        if ($carteActive) {
            $carteActive->setAttribute('geojson', $geojson);
        }

        return view('cartes.index', [
            'vue' => $vue,
            'zone' => $zone,
            'zones' => $zones,
            'regions' => $regions,
            'departements' => $departements,
            'pointsCarte' => $pointsCarte,
            'points' => $points,
            'boutiques' => $boutiques,
            'carteActive' => $carteActive,
            'titreCarte' => $titreCarte,
            'traces' => $traces,
            'boutiquesCount' => count($boutiques),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:'.implode(',', Carte::types()),
            'nom' => 'nullable|string|max:255',
            'geojson' => 'required|file|max:10240',
        ]);

        $collection = $this->collectionFromUpload($request->file('geojson'), $validated['type']);

        $carte = Carte::ofType($validated['type']) ?? new Carte(['type' => $validated['type']]);
        $carte->type = $validated['type'];
        $carte->replaceCollection(
            $collection,
            $request->file('geojson')->getClientOriginalName(),
            $validated['nom'] ?? Carte::defaultName($validated['type'])
        );

        return redirect()
            ->route('cartes.index', ['vue' => $validated['type']])
            ->with('success', 'La carte a été mise à jour à partir du GeoJSON.');
    }

    public function storeRegion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'geojson' => 'required|file|max:10240',
        ]);

        $collection = $this->collectionFromUpload($request->file('geojson'), Carte::TYPE_REGIONS);
        $feature = $collection['features'][0] ?? null;

        if (! $feature) {
            throw ValidationException::withMessages([
                'geojson' => 'Le GeoJSON ne contient aucune géométrie à enregistrer.',
            ]);
        }

        $carte = Carte::ofType(Carte::TYPE_REGIONS) ?? Carte::create([
            'type' => Carte::TYPE_REGIONS,
            'nom' => Carte::defaultName(Carte::TYPE_REGIONS),
            'geojson' => ['type' => 'FeatureCollection', 'features' => []],
            'traces_count' => 0,
        ]);

        if (count($collection['features']) > 1) {
            $carte->replaceCollection(
                $collection,
                $request->file('geojson')->getClientOriginalName(),
                $validated['nom']
            );
        } else {
            $carte->upsertFeature($validated['nom'], $feature);
            $carte->source_filename = $request->file('geojson')->getClientOriginalName();
            $carte->save();
        }

        return redirect()
            ->route('cartes.index', ['vue' => Carte::TYPE_REGIONS])
            ->with('success', 'La région a été enregistrée.');
    }

    public function storePoint(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:255',
        ]);

        $point = CartePoint::create($validated);

        if ($request->expectsJson()) {
            return response()->json($point, 201);
        }

        return redirect()
            ->route('cartes.index', ['vue' => Carte::TYPE_POINTS])
            ->with('success', 'Le point a été enregistré.');
    }

    public function destroyPoint(Request $request, CartePoint $point): RedirectResponse|JsonResponse
    {
        $point->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()
            ->route('cartes.index', ['vue' => Carte::TYPE_POINTS])
            ->with('success', 'Le point a été supprimé.');
    }

    private function requestedVue(Request $request): string
    {
        $vue = (string) $request->query('vue', Carte::TYPE_REGIONS);

        return in_array($vue, Carte::types(), true) ? $vue : Carte::TYPE_REGIONS;
    }

    /**
     * @param  list<string>  $zones
     */
    private function requestedZone(Request $request, array $zones): string
    {
        $zone = trim((string) $request->query('zone', ''));

        foreach ($zones as $nom) {
            if (strcasecmp($nom, $zone) === 0) {
                return $nom;
            }
        }

        return '';
    }

    private function titreZone(string $zone): string
    {
        $label = mb_convert_case(mb_strtolower($zone), MB_CASE_TITLE, 'UTF-8');

        return preg_match('/^[aeiouyàâäéèêëïîôöùûü]/i', $label)
            ? "Carte d'{$label}"
            : "Carte de {$label}";
    }

    /**
     * @param  list<array<string, mixed>>  $zoneFeatures
     * @return list<array<string, mixed>>
     */
    private function boutiquesSurCarte(array $zoneFeatures, bool $filtrerParZone): array
    {
        if (! Schema::hasTable('boutiques')) {
            return [];
        }

        $boutiques = Boutique::query()
            ->when(Schema::hasTable('communes'), fn ($query) => $query->with('commune'))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nom')
            ->get();

        if ($filtrerParZone) {
            $boutiques = $boutiques->filter(function (Boutique $boutique) use ($zoneFeatures) {
                return Carte::containsLatLng(
                    $zoneFeatures,
                    (float) $boutique->latitude,
                    (float) $boutique->longitude
                );
            });
        }

        return $boutiques->map(function (Boutique $boutique) {
            return [
                'id' => $boutique->id,
                'nom' => $boutique->nom,
                'type_articles' => $boutique->type_articles,
                'commune' => $boutique->commune?->nom_commune,
                'latitude' => (float) $boutique->latitude,
                'longitude' => (float) $boutique->longitude,
                'url' => route('boutiques.show', $boutique),
            ];
        })->values()->all();
    }

    private function collectionFromUpload(UploadedFile $file, string $type): array
    {
        $payload = json_decode($file->get(), true);

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'geojson' => 'Le fichier GeoJSON est invalide ou illisible.',
            ]);
        }

        $collection = Carte::enrichFeatureNames(
            Carte::filterFeaturesForType(Carte::normalize($payload), $type)
        );

        if (Carte::featureCount($collection) === 0) {
            throw ValidationException::withMessages([
                'geojson' => $type === Carte::TYPE_POINTS
                    ? 'Le GeoJSON ne contient aucun point.'
                    : 'Le GeoJSON ne contient aucun tracé.',
            ]);
        }

        return $collection;
    }
}

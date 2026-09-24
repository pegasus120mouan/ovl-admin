<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Carte extends Model
{
    public const TYPE_REGIONS = 'regions';

    public const TYPE_DEPARTEMENTS = 'departements';

    public const TYPE_POINTS = 'points';

    protected $fillable = [
        'type',
        'nom',
        'source_filename',
        'geojson',
        'traces_count',
    ];

    protected $casts = [
        'geojson' => 'array',
        'traces_count' => 'integer',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_REGIONS,
            self::TYPE_DEPARTEMENTS,
            self::TYPE_POINTS,
        ];
    }

    public static function ofType(string $type): ?self
    {
        return static::query()->where('type', $type)->first();
    }

    public static function defaultName(string $type): string
    {
        return match ($type) {
            self::TYPE_DEPARTEMENTS => 'Départements',
            self::TYPE_POINTS => 'Points',
            default => 'Régions',
        };
    }

    public static function normalize(array $payload): array
    {
        $type = $payload['type'] ?? null;

        if ($type === 'FeatureCollection' && isset($payload['features']) && is_array($payload['features'])) {
            return [
                'type' => 'FeatureCollection',
                'features' => array_values($payload['features']),
            ];
        }

        if ($type === 'Feature' && isset($payload['geometry'])) {
            return [
                'type' => 'FeatureCollection',
                'features' => [$payload],
            ];
        }

        if (in_array($type, ['Polygon', 'MultiPolygon', 'Point', 'MultiPoint', 'LineString', 'MultiLineString'], true)) {
            return [
                'type' => 'FeatureCollection',
                'features' => [[
                    'type' => 'Feature',
                    'properties' => [],
                    'geometry' => $payload,
                ]],
            ];
        }

        throw ValidationException::withMessages([
            'geojson' => 'Le fichier doit être un GeoJSON valide (FeatureCollection, Feature ou géométrie).',
        ]);
    }

    public static function enrichFeatureNames(array $geojson): array
    {
        foreach ($geojson['features'] ?? [] as $index => $feature) {
            $name = self::featureName($feature, $index);
            $properties = $feature['properties'] ?? [];
            $geojson['features'][$index]['type'] = 'Feature';
            $geojson['features'][$index]['properties'] = array_merge($properties, [
                'nom' => $name,
                'name' => $name,
            ]);
        }

        return $geojson;
    }

    public static function filterFeaturesForType(array $geojson, string $type): array
    {
        $features = $geojson['features'] ?? [];

        if ($features === []) {
            return $geojson;
        }

        if ($type === self::TYPE_POINTS) {
            $geojson['features'] = array_values(array_filter($features, [self::class, 'isPointFeature']));

            return $geojson;
        }

        $filtered = self::featuresWithLevel($features, $type === self::TYPE_DEPARTEMENTS
            ? ['departement', 'département', 'district sanitaire', 'commune']
            : ['region', 'région', 'district']);

        if ($filtered === []) {
            return $geojson;
        }

        $geojson['features'] = $filtered;

        return $geojson;
    }

    /**
     * @param  list<array<string, mixed>>  $features
     * @param  list<string>  $labels
     * @return list<array<string, mixed>>
     */
    private static function featuresWithLevel(array $features, array $labels): array
    {
        $hasLevels = false;

        foreach ($features as $feature) {
            if (trim((string) (($feature['properties']['area_level_label'] ?? ''))) !== '') {
                $hasLevels = true;
                break;
            }
        }

        if (! $hasLevels) {
            return [];
        }

        return array_values(array_filter($features, function (array $feature) use ($labels) {
            $label = strtolower(trim((string) (($feature['properties']['area_level_label'] ?? ''))));

            return in_array($label, $labels, true);
        }));
    }

    private static function isPointFeature(array $feature): bool
    {
        $geometryType = $feature['geometry']['type'] ?? $feature['type'] ?? null;

        return in_array($geometryType, ['Point', 'MultiPoint'], true);
    }

    public static function featureCount(array $geojson): int
    {
        return count($geojson['features'] ?? []);
    }

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    public static function uniqueFeatureNames(array $geojson): array
    {
        $names = [];

        foreach ($geojson['features'] ?? [] as $index => $feature) {
            $name = self::featureName($feature, $index);

            if ($name !== '' && ! in_array($name, $names, true)) {
                $names[] = $name;
            }
        }

        usort($names, function (string $left, string $right) {
            $leftAbidjan = str_starts_with(mb_strtolower($left), 'abidjan') ? 0 : 1;
            $rightAbidjan = str_starts_with(mb_strtolower($right), 'abidjan') ? 0 : 1;

            return $leftAbidjan <=> $rightAbidjan ?: strcasecmp($left, $right);
        });

        return $names;
    }

    public static function filterByZone(array $geojson, ?string $zone, ?array $referenceGeojson = null): array
    {
        $zone = trim((string) $zone);

        if ($zone === '') {
            return $geojson;
        }

        $matched = self::featuresMatchingZone($geojson['features'] ?? [], $zone);

        if ($matched === [] && is_array($referenceGeojson)) {
            $zoneFeatures = self::featuresMatchingZone($referenceGeojson['features'] ?? [], $zone);
            $bounds = self::featuresBounds($zoneFeatures);

            if ($bounds !== null) {
                $matched = array_values(array_filter($geojson['features'] ?? [], function (array $feature) use ($bounds) {
                    return self::featureIntersectsBounds($feature, $bounds);
                }));
            }
        }

        $geojson['features'] = $matched;

        return $geojson;
    }

    public static function containsLatLng(array $features, float $latitude, float $longitude): bool
    {
        $bounds = self::featuresBounds($features);

        if ($bounds === null) {
            return false;
        }

        return $longitude >= $bounds['minLng']
            && $longitude <= $bounds['maxLng']
            && $latitude >= $bounds['minLat']
            && $latitude <= $bounds['maxLat'];
    }

    public static function matchesZone(string $name, string $zone): bool
    {
        $name = mb_strtolower(trim($name));
        $zone = mb_strtolower(trim($zone));

        if ($name === '' || $zone === '') {
            return false;
        }

        return $name === $zone
            || str_starts_with($name, $zone.' ')
            || str_starts_with($name, $zone.'-')
            || str_contains($name, $zone);
    }

    /**
     * @param  list<array<string, mixed>>  $features
     * @return list<array<string, mixed>>
     */
    private static function featuresMatchingZone(array $features, string $zone): array
    {
        $ids = [];

        foreach ($features as $index => $feature) {
            if (self::matchesZone(self::featureName($feature, $index), $zone)) {
                $areaId = $feature['properties']['area_id'] ?? null;

                if (is_string($areaId) && $areaId !== '') {
                    $ids[] = $areaId;
                }
            }
        }

        return array_values(array_filter($features, function (array $feature, int $index) use ($zone, $ids) {
            $parentId = $feature['properties']['parent_area_id'] ?? null;

            return self::matchesZone(self::featureName($feature, $index), $zone)
                || (is_string($parentId) && in_array($parentId, $ids, true));
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param  list<array<string, mixed>>  $features
     * @return array{minLng: float, minLat: float, maxLng: float, maxLat: float}|null
     */
    private static function featuresBounds(array $features): ?array
    {
        $minLng = $minLat = INF;
        $maxLng = $maxLat = -INF;

        foreach ($features as $feature) {
            foreach (self::featureCoordinates($feature) as [$lng, $lat]) {
                $minLng = min($minLng, $lng);
                $minLat = min($minLat, $lat);
                $maxLng = max($maxLng, $lng);
                $maxLat = max($maxLat, $lat);
            }
        }

        if (! is_finite($minLng)) {
            return null;
        }

        return compact('minLng', 'minLat', 'maxLng', 'maxLat');
    }

    /**
     * @param  array{minLng: float, minLat: float, maxLng: float, maxLat: float}  $bounds
     */
    private static function featureIntersectsBounds(array $feature, array $bounds): bool
    {
        $featureBounds = self::featuresBounds([$feature]);

        if ($featureBounds === null) {
            return false;
        }

        return $featureBounds['minLng'] <= $bounds['maxLng']
            && $featureBounds['maxLng'] >= $bounds['minLng']
            && $featureBounds['minLat'] <= $bounds['maxLat']
            && $featureBounds['maxLat'] >= $bounds['minLat'];
    }

    /**
     * @return list<array{0: float, 1: float}>
     */
    private static function featureCoordinates(array $feature): array
    {
        $coordinates = $feature['geometry']['coordinates'] ?? [];
        $points = [];

        $walker = function ($node) use (&$walker, &$points): void {
            if (! is_array($node) || $node === []) {
                return;
            }

            if (isset($node[0], $node[1]) && is_numeric($node[0]) && is_numeric($node[1])) {
                $points[] = [(float) $node[0], (float) $node[1]];

                return;
            }

            foreach ($node as $child) {
                $walker($child);
            }
        };

        $walker($coordinates);

        return $points;
    }

    public static function featureName(array $feature, ?int $index = null): string
    {
        $properties = $feature['properties'] ?? [];

        foreach ([
            'nom', 'area_name', 'NomDistric', 'NomRegion', 'NomCommune',
            'name', 'NAME', 'Nom', 'NAME_1', 'NAME_2', 'NAME_3',
            'shapeName', 'ADM1_FR', 'ADM2_FR', 'ADM1_NAME', 'ADM2_NAME',
            'region', 'departement', 'commune', 'libelle',
        ] as $key) {
            $value = trim((string) ($properties[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return 'Tracé '.(($index ?? 0) + 1);
    }

    public function namedFeatures(): array
    {
        $features = [];

        foreach ($this->geojson['features'] ?? [] as $index => $feature) {
            $features[] = [
                'nom' => self::featureName($feature, $index),
                'feature' => $feature,
            ];
        }

        return $features;
    }

    public function replaceCollection(array $geojson, ?string $filename = null, ?string $nom = null): self
    {
        $this->fill([
            'nom' => $nom ?: $this->nom ?: self::defaultName($this->type),
            'source_filename' => $filename,
            'geojson' => $geojson,
            'traces_count' => self::featureCount($geojson),
        ])->save();

        return $this;
    }

    public function upsertFeature(string $nom, array $feature): self
    {
        $collection = $this->geojson ?? ['type' => 'FeatureCollection', 'features' => []];
        $features = $collection['features'] ?? [];
        $replaced = false;

        $feature['type'] = 'Feature';
        $feature['properties'] = array_merge($feature['properties'] ?? [], ['nom' => $nom, 'name' => $nom]);

        foreach ($features as $index => $existing) {
            if (strcasecmp(self::featureName($existing, $index), $nom) === 0) {
                $features[$index] = $feature;
                $replaced = true;
                break;
            }
        }

        if (! $replaced) {
            $features[] = $feature;
        }

        $collection['type'] = 'FeatureCollection';
        $collection['features'] = array_values($features);

        return $this->replaceCollection($collection, $this->source_filename, $this->nom ?: self::defaultName($this->type));
    }
}

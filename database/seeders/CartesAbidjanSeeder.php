<?php

namespace Database\Seeders;

use App\Models\Carte;
use Illuminate\Database\Seeder;

class CartesAbidjanSeeder extends Seeder
{
    public function run(): void
    {
        $this->store(
            Carte::TYPE_REGIONS,
            'Abidjan',
            'abidjan.geojson',
        );

        $this->store(
            Carte::TYPE_DEPARTEMENTS,
            'Communes d\'Abidjan',
            'abidjan_communes_osm.geojson',
        );

        $points = Carte::ofType(Carte::TYPE_POINTS);

        if ($points) {
            $points->replaceCollection(
                ['type' => 'FeatureCollection', 'features' => []],
                null,
                Carte::defaultName(Carte::TYPE_POINTS)
            );
        }
    }

    private function store(string $type, string $nom, string $filename): void
    {
        $path = database_path('data/'.$filename);

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            throw new \RuntimeException("GeoJSON illisible : {$filename}");
        }

        $collection = Carte::enrichFeatureNames(
            Carte::filterFeaturesForType(Carte::normalize($payload), $type)
        );

        $carte = Carte::ofType($type) ?? new Carte(['type' => $type]);
        $carte->type = $type;
        $carte->replaceCollection($collection, $filename, $nom);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Boutique;
use App\Models\Carte;
use App\Models\CartePoint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CarteManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('boutiques');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('carte_points');
        Schema::dropIfExists('cartes');
        Schema::dropIfExists('reclamations');
        Schema::dropIfExists('commandes');

        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->date('date_reception')->nullable();
            $table->date('date_livraison')->nullable();
            $table->unsignedBigInteger('utilisateur_id')->nullable();
            $table->boolean('point_valide')->default(false);
            $table->dateTime('date_validation_point')->nullable();
            $table->boolean('paiement_effectue')->default(false);
        });

        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->string('statut')->nullable();
        });

        Schema::create('cartes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->unique();
            $table->string('nom');
            $table->string('source_filename')->nullable();
            $table->longText('geojson');
            $table->unsignedInteger('traces_count')->default(0);
            $table->timestamps();
        });

        Schema::create('carte_points', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('communes', function (Blueprint $table) {
            $table->integer('commune_id')->primary();
            $table->string('nom_commune');
        });

        Schema::create('boutiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('logo')->nullable();
            $table->string('type_articles')->nullable();
            $table->boolean('statut')->default(true);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('commune_id')->nullable();
        });
    }

    public function test_gestion_des_cartes_page_displays_map_actions(): void
    {
        $this->get(route('cartes.index'))
            ->assertOk()
            ->assertSee('Importer GeoJSON')
            ->assertSee('Régions')
            ->assertSee('Départements')
            ->assertSee('Enregistrer une région')
            ->assertSee('Localisation des points')
            ->assertSee('Carte des régions importées');
    }

    public function test_selecting_abidjan_tab_shows_only_that_map(): void
    {
        $this->post(route('cartes.import'), [
            'type' => 'regions',
            'geojson' => $this->geojsonFile('districts.geojson', $this->featureCollection([
                $this->polygon('ABIDJAN'),
                $this->polygon('YAMOUSSOUKRO'),
                $this->polygon('LAGUNES'),
            ])),
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        $this->get(route('cartes.index'))
            ->assertOk()
            ->assertSee('Abidjan')
            ->assertSee('Yamoussoukro')
            ->assertSee('3 tracé(s)');

        $this->get(route('cartes.index', ['zone' => 'ABIDJAN']))
            ->assertOk()
            ->assertSee('Carte d\'Abidjan')
            ->assertSee('1 tracé(s)')
            ->assertSee('Abidjan')
            ->assertSee('Yamoussoukro');
    }

    public function test_boutiques_with_coordinates_appear_on_the_selected_zone(): void
    {
        $this->post(route('cartes.import'), [
            'type' => 'regions',
            'geojson' => $this->geojsonFile('districts.geojson', $this->featureCollection([
                $this->polygon('ABIDJAN'),
                $this->polygon('YAMOUSSOUKRO'),
            ])),
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        Boutique::query()->create([
            'nom' => 'Dragonon Map Test',
            'type_articles' => 'Vêtements',
            'latitude' => 5.30,
            'longitude' => -4.00,
        ]);

        Boutique::query()->create([
            'nom' => 'Boutique Nord Test',
            'latitude' => 9.50,
            'longitude' => -5.60,
        ]);

        $this->get(route('cartes.index'))
            ->assertOk()
            ->assertSee('Dragonon Map Test')
            ->assertSee('Boutique Nord Test')
            ->assertSee('2 boutique(s)');

        $this->get(route('cartes.index', ['zone' => 'ABIDJAN']))
            ->assertOk()
            ->assertSee('Dragonon Map Test')
            ->assertSee('1 boutique(s)')
            ->assertDontSee('Boutique Nord Test');
    }

    public function test_feature_name_uses_area_name_and_district_name(): void
    {
        $this->assertSame('Abobo-Est', Carte::featureName([
            'properties' => ['area_name' => 'Abobo-Est', 'area_level_label' => 'District Sanitaire'],
        ]));

        $this->assertSame('Abidjan', Carte::featureName([
            'properties' => ['NomDistric' => 'Abidjan', 'CodDistric' => '01'],
        ]));
    }

    public function test_hierarchical_geojson_is_split_by_map_type(): void
    {
        $file = $this->geojsonFile('civ_areas.geojson', $this->featureCollection([
            $this->namedPolygon('Cote d\'Ivoire', ['area_name' => 'Cote d\'Ivoire', 'area_level_label' => 'Pays']),
            $this->namedPolygon('Abidjan 1', ['area_name' => 'Abidjan 1', 'area_level_label' => 'Region']),
            $this->namedPolygon('Abidjan 2', ['area_name' => 'Abidjan 2', 'area_level_label' => 'Region']),
            $this->namedPolygon('Cocody-Bingerville', ['area_name' => 'Cocody-Bingerville', 'area_level_label' => 'District Sanitaire']),
            $this->namedPolygon('Koumassi', ['area_name' => 'Koumassi', 'area_level_label' => 'District Sanitaire']),
        ]));

        $this->post(route('cartes.import'), [
            'type' => 'regions',
            'geojson' => $file,
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        $regions = Carte::ofType(Carte::TYPE_REGIONS);

        $this->assertSame(2, $regions->traces_count);
        $this->assertSame('Abidjan 1', Carte::featureName($regions->geojson['features'][0]));
        $this->assertSame('Abidjan 2', Carte::featureName($regions->geojson['features'][1]));

        $this->post(route('cartes.import'), [
            'type' => 'departements',
            'geojson' => $this->geojsonFile('civ_areas.geojson', $this->featureCollection([
                $this->namedPolygon('Abidjan 1', ['area_name' => 'Abidjan 1', 'area_level_label' => 'Region']),
                $this->namedPolygon('Cocody-Bingerville', ['area_name' => 'Cocody-Bingerville', 'area_level_label' => 'District Sanitaire']),
                $this->namedPolygon('Koumassi', ['area_name' => 'Koumassi', 'area_level_label' => 'District Sanitaire']),
            ])),
        ])->assertRedirect(route('cartes.index', ['vue' => 'departements']));

        $departements = Carte::ofType(Carte::TYPE_DEPARTEMENTS);

        $this->assertSame(2, $departements->traces_count);
        $this->assertSame('Cocody-Bingerville', Carte::featureName($departements->geojson['features'][0]));
    }

    public function test_polygon_geojson_is_rejected_for_points_map(): void
    {
        $this->from(route('cartes.index'))
            ->post(route('cartes.import'), [
                'type' => 'points',
                'geojson' => $this->geojsonFile('polygons.geojson', $this->featureCollection([
                    $this->polygon('Abidjan'),
                ])),
            ])
            ->assertRedirect(route('cartes.index'))
            ->assertSessionHasErrors('geojson');
    }

    public function test_importing_geojson_replaces_the_regions_map(): void
    {
        $this->post(route('cartes.import'), [
            'type' => 'regions',
            'nom' => 'Régions CI',
            'geojson' => $this->geojsonFile('regions.geojson', $this->featureCollection([
                $this->polygon('Lagunes'),
                $this->polygon('Savanes'),
            ])),
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        $carte = Carte::ofType(Carte::TYPE_REGIONS);

        $this->assertNotNull($carte);
        $this->assertSame(2, $carte->traces_count);
        $this->assertSame('regions.geojson', $carte->source_filename);

        $this->post(route('cartes.import'), [
            'type' => 'regions',
            'geojson' => $this->geojsonFile('regions-v2.geojson', $this->featureCollection([
                $this->polygon('Abidjan'),
            ])),
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        $carte->refresh();

        $this->assertSame(1, $carte->traces_count);
        $this->assertSame('regions-v2.geojson', $carte->source_filename);
        $this->assertSame(1, Carte::count());
    }

    public function test_saving_a_named_region_upserts_the_feature(): void
    {
        $this->post(route('cartes.regions.store'), [
            'nom' => 'Comoé',
            'geojson' => $this->geojsonFile('comoe.geojson', $this->polygon('Comoé')),
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        $this->post(route('cartes.regions.store'), [
            'nom' => 'Comoé',
            'geojson' => $this->geojsonFile('comoe-2.geojson', $this->polygon('Comoé')),
        ])->assertRedirect(route('cartes.index', ['vue' => 'regions']));

        $carte = Carte::ofType(Carte::TYPE_REGIONS);

        $this->assertSame(1, $carte->traces_count);
        $this->assertSame('Comoé', Carte::featureName($carte->geojson['features'][0]));
    }

    public function test_departments_view_uses_the_imported_layer(): void
    {
        $this->post(route('cartes.import'), [
            'type' => 'departements',
            'geojson' => $this->geojsonFile('departements.geojson', $this->featureCollection([
                $this->polygon('Bouaké'),
            ])),
        ])->assertRedirect(route('cartes.index', ['vue' => 'departements']));

        $this->get(route('cartes.index', ['vue' => 'departements']))
            ->assertOk()
            ->assertSee('Carte des départements importés')
            ->assertSee('1 tracé(s)');
    }

    public function test_a_point_can_be_located_on_the_map(): void
    {
        $this->post(route('cartes.points.store'), [
            'nom' => 'Dépôt Abidjan',
            'latitude' => 5.35995,
            'longitude' => -4.00826,
        ])->assertRedirect(route('cartes.index', ['vue' => 'points']));

        $this->assertDatabaseHas('carte_points', [
            'nom' => 'Dépôt Abidjan',
        ]);

        $point = CartePoint::first();

        $this->get(route('cartes.index', ['vue' => 'points']))
            ->assertOk()
            ->assertSee('Localisation des points')
            ->assertSee('1 tracé(s)');

        $this->delete(route('cartes.points.destroy', $point))
            ->assertRedirect(route('cartes.index', ['vue' => 'points']));

        $this->assertDatabaseMissing('carte_points', [
            'id' => $point->id,
        ]);
    }

    public function test_invalid_geojson_is_rejected(): void
    {
        $this->from(route('cartes.index'))
            ->post(route('cartes.import'), [
                'type' => 'regions',
                'geojson' => $this->geojsonFile('invalid.geojson', ['foo' => 'bar']),
            ])
            ->assertRedirect(route('cartes.index'))
            ->assertSessionHasErrors('geojson');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function geojsonFile(string $name, array $payload): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @param  list<array<string, mixed>>  $features
     * @return array<string, mixed>
     */
    private function featureCollection(array $features): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function polygon(string $name): array
    {
        return $this->namedPolygon($name, ['nom' => $name]);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private function namedPolygon(string $name, array $properties): array
    {
        return [
            'type' => 'Feature',
            'properties' => $properties + ['nom' => $name],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [-4.1, 5.2],
                    [-3.9, 5.2],
                    [-3.9, 5.4],
                    [-4.1, 5.4],
                    [-4.1, 5.2],
                ]],
            ],
        ];
    }
}

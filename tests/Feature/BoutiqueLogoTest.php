<?php

namespace Tests\Feature;

use App\Models\Boutique;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BoutiqueLogoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('commandes');
        Schema::dropIfExists('utilisateurs');
        Schema::dropIfExists('boutiques');

        Schema::create('boutiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('logo')->nullable();
            $table->string('type_articles')->nullable();
            $table->boolean('statut')->default(true);
        });

        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable();
            $table->unsignedBigInteger('boutique_id')->nullable();
            $table->boolean('statut_compte')->default(true);
        });

        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id')->nullable();
        });
    }

    public function test_updating_a_boutique_logo_stores_it_on_the_r2_boutiques_folder(): void
    {
        Storage::fake('r2');

        $boutique = Boutique::create([
            'nom' => 'Autres',
            'logo' => 'boutiques/default_boutiques.png',
        ]);

        $logo = UploadedFile::fake()->image('logo-boutique.png', 120, 120);

        $this->put(route('boutiques.update', $boutique), [
            'logo' => $logo,
        ])->assertRedirect(route('boutiques.show', $boutique));

        $boutique->refresh();

        $this->assertNotSame('boutiques/default_boutiques.png', $boutique->logo);
        $this->assertStringStartsWith('boutiques/', $boutique->logo);
        Storage::disk('r2')->assertExists($boutique->logo);
    }
}

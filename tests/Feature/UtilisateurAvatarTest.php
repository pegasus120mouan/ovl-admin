<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UtilisateurAvatarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('utilisateurs');

        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('prenoms')->nullable();
            $table->string('contact')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('statut_compte')->default(true);
        });
    }

    public function test_updating_an_admin_photo_stores_it_on_the_r2_utilisateurs_folder(): void
    {
        Storage::fake('r2');

        $admin = Utilisateur::create([
            'nom' => 'Coulibaly',
            'prenoms' => 'Souleymane',
            'contact' => '0700000000',
            'login' => 'adminr2',
            'password' => hash('sha256', 'secret'),
            'role' => 'admin',
            'avatar' => 'administrateurs/admins.png',
            'statut_compte' => true,
        ]);

        $this->put(route('users.administrateurs.update', $admin), [
            'avatar' => UploadedFile::fake()->image('photo-admin.png', 80, 80),
            'redirect_to' => route('users.administrateurs.show', $admin),
        ])->assertRedirect(route('users.administrateurs.show', $admin));

        $admin->refresh();

        $this->assertStringStartsWith('utilisateurs/', $admin->avatar);
        Storage::disk('r2')->assertExists($admin->avatar);
    }

    public function test_creating_an_admin_with_photo_stores_it_on_the_r2_utilisateurs_folder(): void
    {
        Storage::fake('r2');

        $this->post(route('users.administrateurs.store'), [
            'nom' => 'Kone',
            'prenoms' => 'Awa',
            'contact' => '0700000001',
            'login' => 'awa.admin',
            'password' => 'secret',
            'statut_compte' => 1,
            'avatar' => UploadedFile::fake()->image('nouvelle-photo.jpg', 80, 80),
        ])->assertRedirect(route('users.administrateurs'));

        $admin = Utilisateur::query()->where('login', 'awa.admin')->first();

        $this->assertNotNull($admin);
        $this->assertStringStartsWith('utilisateurs/', $admin->avatar);
        Storage::disk('r2')->assertExists($admin->avatar);
    }
}

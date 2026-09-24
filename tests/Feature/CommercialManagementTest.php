<?php

namespace Tests\Feature;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\Commission;
use App\Models\PaiementCommission;
use App\Models\Utilisateur;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommercialManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('bordereau_commission_colis');
        Schema::dropIfExists('paiements_commissions');
        Schema::dropIfExists('bordereaux_commissions');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commandes');
        Schema::dropIfExists('reclamations');
        Schema::dropIfExists('utilisateurs');
        Schema::dropIfExists('boutiques');

        Schema::create('boutiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('statut')->default(true);
        });

        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('prenoms')->nullable();
            $table->string('contact')->nullable();
            $table->string('login')->nullable();
            $table->string('code_commercial', 20)->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->unsignedBigInteger('boutique_id')->nullable();
            $table->unsignedBigInteger('commercial_id')->nullable();
            $table->boolean('statut_compte')->default(true);
            $table->string('avatar')->nullable();
        });

        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id')->nullable();
            $table->string('communes')->nullable();
            $table->integer('cout_global')->default(0);
            $table->integer('cout_livraison')->default(0);
            $table->integer('cout_reel')->default(0);
            $table->string('statut')->default('Non Livré');
            $table->date('date_reception')->nullable();
            $table->date('date_livraison')->nullable();
            $table->boolean('point_valide')->default(false);
            $table->dateTime('date_validation_point')->nullable();
            $table->boolean('paiement_effectue')->default(false);
        });

        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->string('statut')->nullable();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->decimal('taux', 5, 2);
        });

        Schema::create('paiements_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commercial_id');
            $table->unsignedBigInteger('bordereau_id')->nullable();
            $table->date('periode');
            $table->integer('montant')->default(0);
            $table->date('date_paiement')->nullable();
            $table->string('mode')->nullable();
            $table->string('statut')->nullable();
            $table->string('recu')->nullable();
        });

        Schema::create('bordereaux_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->unsignedBigInteger('commercial_id');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->dateTime('genere_le');
            $table->unsignedInteger('nb_colis')->default(0);
            $table->integer('base_livraison')->default(0);
            $table->integer('montant')->default(0);
        });

        Schema::create('bordereau_commission_colis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bordereau_id');
            $table->unsignedBigInteger('commande_id')->unique();
            $table->integer('montant')->default(0);
        });
    }

    public function test_creating_a_commercial_generates_a_code(): void
    {
        $this->post('/users/commerciaux', [
            'nom' => 'Kone',
            'prenoms' => 'Awa',
            'contact' => '0700000000',
            'login' => 'awa.kone',
            'password' => 'secret',
            'statut_compte' => 1,
        ])->assertRedirect(route('users.commerciaux'));

        $commercial = Utilisateur::query()->where('login', 'awa.kone')->first();

        $this->assertNotNull($commercial);
        $this->assertSame('commercial', $commercial->role);
        $this->assertSame($commercial->defaultCommercialCode(), $commercial->code_commercial);
    }

    public function test_creating_a_commercial_can_use_a_custom_code(): void
    {
        $this->post('/users/commerciaux', [
            'nom' => 'Kone',
            'prenoms' => 'Awa',
            'contact' => '0700000000',
            'login' => 'awa.kone',
            'code_commercial' => 'com-awa',
            'password' => 'secret',
        ])->assertRedirect(route('users.commerciaux'));

        $this->assertDatabaseHas('utilisateurs', [
            'login' => 'awa.kone',
            'code_commercial' => 'COM-AWA',
        ]);
    }

    public function test_montant_commerciaux_page_lists_dues_and_payments(): void
    {
        $commercial = $this->createCommercial();
        $this->createDeliveredCommande($commercial, 1500, '2026-09-19');
        Commission::query()->create([
            'taux' => 5,
        ]);
        PaiementCommission::query()->create([
            'commercial_id' => $commercial->id,
            'periode' => '2026-08-01',
            'montant' => 25,
            'date_paiement' => '2026-08-20',
        ]);

        $this->get('/montant-commerciaux')
            ->assertOk()
            ->assertSee('Montant des commerciaux')
            ->assertSee('Liste des commerciaux')
            ->assertSee('Doe Pauline')
            ->assertSee('COM-001')
            ->assertSee('75 FCFA')
            ->assertSee('25 FCFA')
            ->assertSee('50 FCFA')
            ->assertSee('Bordereaux')
            ->assertSee('Paramétrer la commission')
            ->assertSee('Un seul taux pour tous les commerciaux')
            ->assertSee(route('montant-commerciaux.show', $commercial->code_commercial), false);
    }

    public function test_montant_commerciaux_shows_solde_when_nothing_is_due(): void
    {
        $this->createCommercial();

        $this->get('/montant-commerciaux')
            ->assertOk()
            ->assertSee('Soldé')
            ->assertDontSee('Reste à payer FCFA');
    }

    public function test_commercial_name_opens_bordereaux_page(): void
    {
        $commercial = $this->createCommercial();
        $this->createDeliveredCommande($commercial, 1500, '2026-09-19');
        Commission::query()->create(['taux' => 10]);

        $this->get('/montant-commerciaux/'.$commercial->code_commercial)
            ->assertOk()
            ->assertSee('Doe Pauline')
            ->assertSee('Gestion bordereaux')
            ->assertSee('Générer un bordereau')
            ->assertSee('Montant dû mois par mois')
            ->assertSee('Paiements et avances')
            ->assertSee('09/2026')
            ->assertSee('150 FCFA')
            ->assertSee('Aucun bordereau généré pour ce commercial')
            ->assertSee('Aucun paiement enregistré');
    }

    public function test_admin_can_generate_and_pay_a_bordereau(): void
    {
        $commercial = $this->createCommercial();
        $this->createDeliveredCommande($commercial, 1500, '2026-09-19');
        Commission::query()->create(['taux' => 10]);

        $this->post('/montant-commerciaux/'.$commercial->id.'/bordereaux', [
            'date_debut' => '2026-09-01',
            'date_fin' => '2026-09-30',
        ])->assertRedirect(route('montant-commerciaux.show', $commercial));

        $bordereau = \App\Models\BordereauCommission::query()->first();
        $this->assertNotNull($bordereau);
        $this->assertSame(150, (int) $bordereau->montant);
        $this->assertSame(1, (int) $bordereau->nb_colis);

        $this->post('/montant-commerciaux/'.$commercial->id.'/bordereaux/'.$bordereau->id.'/paiement', [
            'mode' => 'Espèces',
            'recu' => 'REC-1',
        ])->assertRedirect(route('montant-commerciaux.show', $commercial));

        $this->assertDatabaseHas('paiements_commissions', [
            'commercial_id' => $commercial->id,
            'bordereau_id' => $bordereau->id,
            'montant' => 150,
            'mode' => 'Espèces',
        ]);

        $this->get('/montant-commerciaux/'.$commercial->code_commercial)
            ->assertOk()
            ->assertSee('Soldé')
            ->assertSee($bordereau->numero)
            ->assertSee('REC-1');
    }

    public function test_commerciaux_list_shows_clickable_code(): void
    {
        $commercial = $this->createCommercial();
        Storage::fake('r2');

        $this->get('/users/commerciaux')
            ->assertOk()
            ->assertSee('Photo')
            ->assertSee('COM-001')
            ->assertSee('Photo '.$commercial->prenoms.' '.$commercial->nom, false)
            ->assertSee(route('users.commerciaux.show', $commercial->code_commercial), false);
    }

    public function test_commercial_profile_allows_editing_photo_and_information(): void
    {
        $commercial = $this->createCommercial();

        $this->get('/users/commerciaux/'.$commercial->code_commercial)
            ->assertOk()
            ->assertSee('COM-001')
            ->assertSee('Doe Pauline')
            ->assertSee('pauline')
            ->assertSee('Photo commercial')
            ->assertSee('Changer le nom')
            ->assertSee('Contact / Statut')
            ->assertSee('Changer le mot de passe')
            ->assertSee('Mettre à jour la photo');
    }

    public function test_admin_can_update_commercial_information_from_profile(): void
    {
        $commercial = $this->createCommercial();

        $this->put('/users/commerciaux/'.$commercial->id, [
            'nom' => 'Yao',
            'prenoms' => 'Pauline',
            'redirect_to' => route('users.commerciaux.show', $commercial),
        ])->assertRedirect(route('users.commerciaux.show', $commercial));

        $this->assertDatabaseHas('utilisateurs', [
            'id' => $commercial->id,
            'nom' => 'Yao',
            'prenoms' => 'Pauline',
        ]);
    }

    public function test_admin_can_update_commercial_photo_on_r2(): void
    {
        Storage::fake('r2');
        $commercial = $this->createCommercial();

        $this->put('/users/commerciaux/'.$commercial->id, [
            'avatar' => UploadedFile::fake()->image('photo-commercial.png', 80, 80),
            'redirect_to' => route('users.commerciaux.show', $commercial),
        ])->assertRedirect(route('users.commerciaux.show', $commercial));

        $commercial->refresh();
        $this->assertStringStartsWith('utilisateurs/', $commercial->avatar);
        Storage::disk('r2')->assertExists($commercial->avatar);
    }

    public function test_admin_can_set_a_global_commission_rate(): void
    {
        $this->createCommercial();

        $this->put('/montant-commerciaux/taux', [
            'taux' => 8.5,
        ])->assertRedirect(route('montant-commerciaux.index'));

        $this->assertSame(1, Commission::query()->count());
        $this->assertDatabaseHas('commissions', [
            'taux' => 8.5,
        ]);

        $this->put('/montant-commerciaux/taux', [
            'taux' => 10,
        ])->assertRedirect(route('montant-commerciaux.index'));

        $this->assertSame(1, Commission::query()->count());
        $this->assertDatabaseHas('commissions', [
            'taux' => 10,
        ]);
    }

    public function test_admin_can_pay_monthly_commission(): void
    {
        $commercial = $this->createCommercial();
        $this->createDeliveredCommande($commercial, 1500, '2026-09-19');
        Commission::query()->create([
            'taux' => 5,
        ]);

        $this->post('/users/commerciaux/'.$commercial->id.'/commissions/paiement', [
            'periode' => '2026-09-01',
        ])->assertRedirect(route('users.commerciaux.show', $commercial));

        $this->assertDatabaseHas('paiements_commissions', [
            'commercial_id' => $commercial->id,
            'montant' => 75,
        ]);

        $this->get('/users/commerciaux/'.$commercial->code_commercial)
            ->assertOk()
            ->assertSee('COM-001');
    }

    public function test_admin_cannot_pay_the_same_month_twice(): void
    {
        $commercial = $this->createCommercial();
        $this->createDeliveredCommande($commercial, 1500, '2026-09-19');
        Commission::query()->create([
            'taux' => 5,
        ]);
        PaiementCommission::query()->create([
            'commercial_id' => $commercial->id,
            'periode' => '2026-09-01',
            'montant' => 75,
            'date_paiement' => '2026-09-20',
        ]);

        $this->from('/users/commerciaux/'.$commercial->id)
            ->post('/users/commerciaux/'.$commercial->id.'/commissions/paiement', [
                'periode' => '2026-09-01',
            ])
            ->assertRedirect('/users/commerciaux/'.$commercial->id)
            ->assertSessionHas('error');

        $this->assertSame(1, PaiementCommission::query()->where('commercial_id', $commercial->id)->count());
    }

    private function createCommercial(): Utilisateur
    {
        $commercial = Utilisateur::query()->create([
            'nom' => 'Doe',
            'prenoms' => 'Pauline',
            'contact' => '0700000001',
            'login' => 'pauline',
            'password' => hash('sha256', 'secret'),
            'role' => 'commercial',
            'statut_compte' => true,
        ]);

        $commercial->assignDefaultCommercialCode();

        return $commercial->fresh();
    }

    private function createClientFor(Utilisateur $commercial, string $nom, string $prenoms, string $boutiqueNom): Utilisateur
    {
        $boutique = Boutique::query()->create([
            'nom' => $boutiqueNom,
            'statut' => true,
        ]);

        return Utilisateur::query()->create([
            'nom' => $nom,
            'prenoms' => $prenoms,
            'contact' => '0700000099',
            'login' => strtolower($nom).'.client',
            'role' => 'clients',
            'boutique_id' => $boutique->id,
            'commercial_id' => $commercial->id,
            'statut_compte' => true,
        ]);
    }

    private function createDeliveredCommande(Utilisateur $commercial, int $coutLivraison, string $dateLivraison): Commande
    {
        $client = Utilisateur::query()
            ->where('commercial_id', $commercial->id)
            ->where('role', 'clients')
            ->first() ?: $this->createClientFor($commercial, 'Yao', 'Fisher', 'Dragron');

        return Commande::query()->create([
            'utilisateur_id' => $client->id,
            'communes' => 'plateau',
            'cout_global' => 18000,
            'cout_livraison' => $coutLivraison,
            'cout_reel' => 18000 - $coutLivraison,
            'statut' => 'Livré',
            'date_reception' => $dateLivraison,
            'date_livraison' => $dateLivraison,
        ]);
    }
}

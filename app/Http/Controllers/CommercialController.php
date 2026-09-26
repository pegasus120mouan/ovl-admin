<?php

namespace App\Http\Controllers;

use App\Models\BordereauCommission;
use App\Models\Boutique;
use App\Models\Commande;
use App\Models\Commission;
use App\Models\ObjectifCommercial;
use App\Models\PaiementCommission;
use App\Models\Utilisateur;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommercialController extends Controller
{
    public function montant(Request $request)
    {
        $regle = Commission::globale();
        $mois = $this->moisDemande($request);
        $periode = Carbon::createFromFormat('!Y-m', $mois)->startOfMonth();
        $commerciaux = Utilisateur::query()
            ->commerciaux()
            ->with('paiementsCommissions')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        $commercialIds = $commerciaux->pluck('id');
        $bases = $commercialIds->isEmpty()
            ? collect()
            : Commande::query()
                ->join('utilisateurs as clients', 'clients.id', '=', 'commandes.utilisateur_id')
                ->where('clients.role', 'clients')
                ->whereIn('clients.commercial_id', $commercialIds)
                ->where('commandes.statut', 'Livré')
                ->whereNotNull('commandes.date_livraison')
                ->selectRaw('clients.commercial_id, SUM(commandes.cout_livraison) as base')
                ->groupBy('clients.commercial_id')
                ->pluck('base', 'commercial_id');

        $objectifColis = ObjectifCommercial::query()
            ->whereDate('periode', $periode->toDateString())
            ->value('montant');
        $objectifColis = $objectifColis === null ? null : (int) $objectifColis;

        $fin = $periode->copy()->endOfMonth();
        $colisMois = $commercialIds->isEmpty()
            ? collect()
            : Commande::query()
                ->join('utilisateurs as clients', 'clients.id', '=', 'commandes.utilisateur_id')
                ->where('clients.role', 'clients')
                ->whereIn('clients.commercial_id', $commercialIds)
                ->where('commandes.statut', 'Livré')
                ->whereNotNull('commandes.date_livraison')
                ->whereDate('commandes.date_livraison', '>=', $periode->toDateString())
                ->whereDate('commandes.date_livraison', '<=', $fin->toDateString())
                ->selectRaw('clients.commercial_id, COUNT(commandes.id) as colis')
                ->groupBy('clients.commercial_id')
                ->pluck('colis', 'commercial_id');

        $commerciaux->getCollection()->transform(function (Utilisateur $commercial) use ($bases, $regle, $colisMois) {
            $base = (int) ($bases[$commercial->id] ?? 0);
            $montantDu = $regle ? $regle->montantPourBase($base) : 0;
            $montantPaye = (int) $commercial->paiementsCommissions->sum('montant');

            $commercial->montant_du = $montantDu;
            $commercial->montant_paye = $montantPaye;
            $commercial->reste_a_payer = max(0, $montantDu - $montantPaye);
            $commercial->colis_livres = (int) ($colisMois[$commercial->id] ?? 0);

            return $commercial;
        });

        return view('users.montant_commerciaux', compact('commerciaux', 'regle', 'mois', 'objectifColis'));
    }

    public function updateObjectif(Request $request)
    {
        $validated = $request->validate([
            'mois' => ['nullable', 'date_format:Y-m', 'required_without:mois_num'],
            'mois_num' => ['nullable', 'integer', 'min:1', 'max:12', 'required_without:mois'],
            'annee' => ['nullable', 'integer', 'min:2000', 'max:2100', 'required_with:mois_num'],
            'montant' => ['required', 'integer', 'min:0'],
        ]);

        $mois = $this->moisDemande($request);
        $periode = Carbon::createFromFormat('!Y-m', $mois)->startOfMonth()->toDateString();
        $montant = (int) $validated['montant'];

        $objectif = ObjectifCommercial::query()->whereDate('periode', $periode)->first();
        if ($objectif) {
            $objectif->update(['montant' => $montant]);
        } else {
            ObjectifCommercial::query()->create([
                'periode' => $periode,
                'montant' => $montant,
            ]);
        }

        return redirect()
            ->route('montant-commerciaux.index', ['mois' => $mois])
            ->with('success', 'Objectif de colis livrés enregistré pour tous les commerciaux.');
    }

    public function situation(Utilisateur $commercial)
    {
        $this->ensureCommercial($commercial);

        $regle = Commission::globale();
        $commandesLivrees = $this->commandesLivrees($commercial->id)
            ->orderByDesc('date_livraison')
            ->get();

        $bordereaux = BordereauCommission::query()
            ->where('commercial_id', $commercial->id)
            ->with('paiements')
            ->orderByDesc('genere_le')
            ->get();

        $paiements = PaiementCommission::query()
            ->where('commercial_id', $commercial->id)
            ->with('bordereau')
            ->orderByDesc('date_paiement')
            ->orderByDesc('id')
            ->get();

        $montantsMensuels = $commandesLivrees
            ->groupBy(fn (Commande $commande) => optional($commande->date_livraison)->format('Y-m-01') ?: 'sans-date')
            ->map(function ($commandes, $periode) use ($regle, $paiements) {
                $base = (int) $commandes->sum('cout_livraison');
                $montantDu = $regle ? $regle->montantPourBase($base) : 0;
                $montantPaye = (int) $paiements
                    ->filter(fn (PaiementCommission $paiement) => $paiement->periode?->format('Y-m-01') === $periode)
                    ->sum('montant');

                return [
                    'periode' => $periode,
                    'colis' => $commandes->count(),
                    'base' => $base,
                    'montant_du' => $montantDu,
                    'montant_paye' => $montantPaye,
                    'reste' => max(0, $montantDu - $montantPaye),
                ];
            })
            ->sortKeysDesc()
            ->values();

        $objectifs = ObjectifCommercial::query()
            ->get()
            ->keyBy(fn (ObjectifCommercial $objectif) => $objectif->periode->format('Y-m-01'));

        $montantsMensuels = $montantsMensuels->map(function (array $ligne) use ($objectifs) {
            $ligne['objectif'] = $objectifs->has($ligne['periode'])
                ? (int) $objectifs[$ligne['periode']]->montant
                : null;

            return $ligne;
        });

        foreach ($objectifs as $periode => $objectif) {
            if ($montantsMensuels->contains(fn (array $ligne) => $ligne['periode'] === $periode)) {
                continue;
            }

            $montantsMensuels->push([
                'periode' => $periode,
                'colis' => 0,
                'base' => 0,
                'montant_du' => 0,
                'montant_paye' => 0,
                'reste' => 0,
                'objectif' => (int) $objectif->montant,
            ]);
        }

        $montantsMensuels = $montantsMensuels->sortByDesc('periode')->values();

        $stats = [
            'montant_du' => (int) $montantsMensuels->sum('montant_du'),
            'montant_paye' => (int) $paiements->sum('montant'),
        ];
        $stats['reste_a_payer'] = max(0, $stats['montant_du'] - $stats['montant_paye']);

        return view('users.montant_commerciaux_show', compact(
            'commercial',
            'regle',
            'montantsMensuels',
            'bordereaux',
            'paiements',
            'stats'
        ));
    }

    public function genererBordereau(Request $request, Utilisateur $commercial)
    {
        $this->ensureCommercial($commercial);

        $validated = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        ]);

        $regle = Commission::globale();
        if (! $regle) {
            return redirect()
                ->route('montant-commerciaux.show', $commercial)
                ->with('error', 'Programmez d’abord le taux de commission.');
        }

        $dejaInclus = DB::table('bordereau_commission_colis')->pluck('commande_id');

        $commandes = $this->commandesLivrees($commercial->id)
            ->whereDate('date_livraison', '>=', $validated['date_debut'])
            ->whereDate('date_livraison', '<=', $validated['date_fin'])
            ->when($dejaInclus->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $dejaInclus))
            ->get();

        if ($commandes->isEmpty()) {
            return redirect()
                ->route('montant-commerciaux.show', $commercial)
                ->with('error', 'Aucun colis livré disponible sur cette période.');
        }

        $bordereau = DB::transaction(function () use ($commercial, $validated, $commandes, $regle) {
            $base = (int) $commandes->sum('cout_livraison');
            $montant = $regle->montantPourBase($base);
            $code = $commercial->code_commercial ?: $commercial->defaultCommercialCode();
            $prefix = 'BR-'.$code.'-'.now()->format('Ym');
            $suite = BordereauCommission::query()->where('numero', 'like', $prefix.'-%')->count() + 1;

            $bordereau = BordereauCommission::query()->create([
                'numero' => $prefix.'-'.str_pad((string) $suite, 3, '0', STR_PAD_LEFT),
                'commercial_id' => $commercial->id,
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'genere_le' => now(),
                'nb_colis' => $commandes->count(),
                'base_livraison' => $base,
                'montant' => $montant,
            ]);

            $bordereau->commandes()->attach(
                $commandes->mapWithKeys(fn (Commande $commande) => [
                    $commande->id => ['montant' => $regle->montantPour($commande)],
                ])->all()
            );

            return $bordereau;
        });

        return redirect()
            ->route('montant-commerciaux.show', $commercial)
            ->with('success', 'Bordereau '.$bordereau->numero.' généré.');
    }

    public function payerBordereau(Request $request, Utilisateur $commercial, BordereauCommission $bordereau)
    {
        $this->ensureCommercial($commercial);
        $this->ensureBordereau($commercial, $bordereau);

        $validated = $request->validate([
            'mode' => ['required', 'string', 'max:50'],
            'recu' => ['nullable', 'string', 'max:80'],
        ]);

        $reste = $bordereau->resteAPayer();
        if ($reste <= 0) {
            return redirect()
                ->route('montant-commerciaux.show', $commercial)
                ->with('error', 'Ce bordereau est déjà soldé.');
        }

        PaiementCommission::query()->create([
            'commercial_id' => $commercial->id,
            'bordereau_id' => $bordereau->id,
            'periode' => $bordereau->date_debut->toDateString(),
            'montant' => $reste,
            'date_paiement' => now()->toDateString(),
            'mode' => $validated['mode'],
            'statut' => 'Validé',
            'recu' => $validated['recu'] ?: null,
        ]);

        return redirect()
            ->route('montant-commerciaux.show', $commercial)
            ->with('success', 'Paiement de '.number_format($reste, 0, ',', ' ').' FCFA enregistré.');
    }

    public function annulerPaiementBordereau(Utilisateur $commercial, PaiementCommission $paiement)
    {
        $this->ensureCommercial($commercial);
        abort_unless((int) $paiement->commercial_id === (int) $commercial->id, 404);

        $paiement->delete();

        return redirect()
            ->route('montant-commerciaux.show', $commercial)
            ->with('success', 'Paiement annulé.');
    }

    public function imprimerBordereau(Utilisateur $commercial, BordereauCommission $bordereau)
    {
        $this->ensureCommercial($commercial);
        $this->ensureBordereau($commercial, $bordereau);

        $bordereau->load(['commandes.client.boutique', 'paiements']);
        $regle = Commission::globale();

        $pdf = Pdf::loadView('users.bordereau_commission_print', [
            'commercial' => $commercial,
            'bordereau' => $bordereau,
            'regle' => $regle,
        ]);

        return $pdf->stream($bordereau->numero.'.pdf');
    }

    public function show(Utilisateur $commercial)
    {
        $this->ensureCommercial($commercial);

        $commerciauxTotal = Utilisateur::query()->commerciaux()->count();
        $commerciauxActifs = Utilisateur::query()->commerciaux()->where('statut_compte', 1)->count();
        $commerciauxInactifs = Utilisateur::query()->commerciaux()->where('statut_compte', 0)->count();
        $boutiquesTotal = Boutique::query()->count();

        $avatarKey = $commercial->avatar ?: null;
        if (! $avatarKey || $avatarKey === 'default.jpg') {
            $avatarKey = 'utilisateurs/utilisateurs.png';
        } elseif (! str_contains($avatarKey, '/')) {
            $avatarKey = 'utilisateurs/'.$avatarKey;
        }

        $avatarUrl = asset('img/logo/logo.png');
        try {
            $disk = Storage::disk('r2');
            $avatarUrl = method_exists($disk, 'temporaryUrl')
                ? $disk->temporaryUrl($avatarKey, now()->addMinutes(30))
                : $disk->url($avatarKey);
        } catch (\Throwable) {
            try {
                $avatarUrl = Storage::disk('r2')->url($avatarKey);
            } catch (\Throwable) {
                //
            }
        }

        return view('users.commerciaux_show', compact(
            'commercial',
            'avatarUrl',
            'commerciauxTotal',
            'commerciauxActifs',
            'commerciauxInactifs',
            'boutiquesTotal'
        ));
    }

    public function updateCommission(Request $request)
    {
        $validated = $request->validate([
            'taux' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Commission::enregistrer((float) $validated['taux']);

        return redirect()
            ->route('montant-commerciaux.index')
            ->with('success', 'Taux de commission enregistré pour tous les commerciaux');
    }

    public function payerCommission(Request $request, Utilisateur $commercial)
    {
        $this->ensureCommercial($commercial);

        $validated = $request->validate([
            'periode' => ['required', 'date_format:Y-m-d'],
        ]);

        $regle = Commission::globale();
        if (! $regle) {
            return redirect()
                ->route('users.commerciaux.show', $commercial)
                ->with('error', 'Programmez d’abord le taux de commission.');
        }

        $periode = Carbon::parse($validated['periode'])->startOfMonth()->toDateString();

        if (PaiementCommission::query()->where('commercial_id', $commercial->id)->whereDate('periode', $periode)->exists()) {
            return redirect()
                ->route('users.commerciaux.show', $commercial)
                ->with('error', 'Ce mois est déjà payé.');
        }

        $debut = Carbon::parse($periode)->startOfMonth();
        $fin = Carbon::parse($periode)->endOfMonth();

        $base = (int) Commande::query()
            ->forCommercial($commercial->id)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', '>=', $debut->toDateString())
            ->whereDate('date_livraison', '<=', $fin->toDateString())
            ->sum('cout_livraison');

        $montant = $regle->montantPourBase($base);
        if ($montant <= 0) {
            return redirect()
                ->route('users.commerciaux.show', $commercial)
                ->with('error', 'Aucune commission à payer pour ce mois.');
        }

        PaiementCommission::query()->create([
            'commercial_id' => $commercial->id,
            'periode' => $periode,
            'montant' => $montant,
            'date_paiement' => now()->toDateString(),
        ]);

        return redirect()
            ->route('users.commerciaux.show', $commercial)
            ->with('success', 'Commission de '.number_format($montant, 0, ',', ' ').' FCFA payée.');
    }

    public function annulerPaiement(Request $request, Utilisateur $commercial)
    {
        $this->ensureCommercial($commercial);

        $validated = $request->validate([
            'periode' => ['required', 'date_format:Y-m-d'],
        ]);

        $periode = Carbon::parse($validated['periode'])->startOfMonth()->toDateString();

        PaiementCommission::query()
            ->where('commercial_id', $commercial->id)
            ->whereDate('periode', $periode)
            ->delete();

        return redirect()
            ->route('users.commerciaux.show', $commercial)
            ->with('success', 'Paiement annulé.');
    }

    private function commandesLivrees(int $commercialId)
    {
        return Commande::query()
            ->forCommercial($commercialId)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison');
    }

    private function ensureBordereau(Utilisateur $commercial, BordereauCommission $bordereau): void
    {
        abort_unless((int) $bordereau->commercial_id === (int) $commercial->id, 404);
    }

    private function moisDemande(Request $request): string
    {
        $mois = (string) $request->input('mois', '');
        if (preg_match('/^\d{4}-\d{2}$/', $mois) === 1) {
            return $mois;
        }

        $annee = (int) $request->input('annee');
        $numero = (int) $request->input('mois_num');
        if ($annee >= 2000 && $annee <= 2100 && $numero >= 1 && $numero <= 12) {
            return sprintf('%04d-%02d', $annee, $numero);
        }

        return now()->format('Y-m');
    }

    private function ensureCommercial(Utilisateur $commercial): void
    {
        abort_unless(($commercial->role ?? null) === 'commercial', 404);
    }
}

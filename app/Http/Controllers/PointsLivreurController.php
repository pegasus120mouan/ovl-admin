<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\CoutLivraison;
use App\Models\PointsLivreur;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class PointsLivreurController extends Controller
{
    public function index(Request $request)
    {
        PointsLivreur::consolidateAllDuplicates();

        $perPage = $request->get('per_page', 20);
        $date = $request->get('date');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $utilisateurId = $request->get('utilisateur_id');
        
        // Points livreurs avec pagination
        $query = PointsLivreur::with('livreur');
        $statsQuery = PointsLivreur::query();
        
        if ($date) {
            $query->whereDate('date_commande', $date);
            $statsQuery->whereDate('date_commande', $date);
        }

        if ($dateDebut) {
            $query->whereDate('date_commande', '>=', $dateDebut);
            $statsQuery->whereDate('date_commande', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('date_commande', '<=', $dateFin);
            $statsQuery->whereDate('date_commande', '<=', $dateFin);
        }

        if ($utilisateurId) {
            $query->where('utilisateur_id', $utilisateurId);
            $statsQuery->where('utilisateur_id', $utilisateurId);
        }
        
        $pointsLivreurs = $query->orderBy('date_commande', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        
        // Données pour les formulaires
        $livreurs = Utilisateur::livreurs()->get();
        
        // Statistiques
        $totalRecette = (clone $statsQuery)->sum('recette');
        $totalDepense = (clone $statsQuery)->sum('depense');
        $totalGain = (clone $statsQuery)->sum('gain_jour');
        $nombreLivreurs = (clone $statsQuery)->distinct('utilisateur_id')->count('utilisateur_id');
        
        return view('points_livreurs.index', compact(
            'pointsLivreurs',
            'livreurs',
            'date',
            'totalRecette',
            'totalDepense',
            'totalGain',
            'nombreLivreurs'
        ));
    }

    public function montantLivreurs(Request $request)
    {
        $perPage = $request->integer('per_page', 20);
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $dateDebut = Carbon::now()->startOfYear()->toDateString();
        $dateFin = Carbon::today()->toDateString();

        $livreurs = Utilisateur::query()
            ->livreurs()
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->paginate($perPage)
            ->withQueryString();

        $livreurIds = $livreurs->pluck('id');

        $montantsMois = Commande::query()
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])
            ->whereIn('livreur_id', $livreurIds)
            ->selectRaw('livreur_id, SUM(cout_livraison) as montant, COUNT(*) as nb_colis')
            ->groupBy('livreur_id')
            ->get()
            ->keyBy('livreur_id');

        $commandesParLivreurJour = Commande::query()
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin)
            ->whereIn('livreur_id', $livreurIds)
            ->selectRaw('livreur_id, DATE(date_livraison) as jour, SUM(cout_global) as montant_global')
            ->groupBy('livreur_id', 'jour')
            ->get()
            ->groupBy('livreur_id');

        $pointsParLivreurJour = PointsLivreur::query()
            ->whereIn('utilisateur_id', $livreurIds)
            ->whereDate('date_commande', '>=', $dateDebut)
            ->whereDate('date_commande', '<=', $dateFin)
            ->selectRaw('utilisateur_id, DATE(date_commande) as jour, SUM(depense) as depense, SUM(montant_verse) as montant_verse')
            ->groupBy('utilisateur_id', 'jour')
            ->get()
            ->groupBy('utilisateur_id');

        $livreurs->getCollection()->transform(function ($livreur) use ($montantsMois, $commandesParLivreurJour, $pointsParLivreurJour) {
            $stats = $montantsMois->get($livreur->id);
            $livreur->montant_mois = (int) ($stats->montant ?? 0);
            $livreur->nb_colis_mois = (int) ($stats->nb_colis ?? 0);

            $commandesJours = $commandesParLivreurJour->get($livreur->id, collect());
            $pointsJours = $pointsParLivreurJour->get($livreur->id, collect());

            $jours = $commandesJours->pluck('jour')
                ->merge($pointsJours->pluck('jour'))
                ->unique();

            $montantAPayer = 0;
            $montantPaye = 0;

            foreach ($jours as $jour) {
                $montantGlobal = (int) ($commandesJours->firstWhere('jour', $jour)->montant_global ?? 0);
                $pointJour = $pointsJours->firstWhere('jour', $jour);
                $depense = (int) ($pointJour->depense ?? 0);
                $montantVerse = (int) ($pointJour->montant_verse ?? 0);

                $montantAPayer += max(0, $montantGlobal - $depense);
                $montantPaye += $montantVerse;
            }

            $livreur->montant_a_payer = $montantAPayer;
            $livreur->montant_paye = $montantPaye;
            $livreur->reste_a_payer = max(0, $montantAPayer - $montantPaye);

            return $livreur;
        });

        $livreursActifs = Utilisateur::query()->livreurs()->where('statut_compte', 1)->count();
        $livreursInactifs = Utilisateur::query()->livreurs()->where('statut_compte', 0)->count();
        $totalMontantMois = (int) Commande::query()
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])
            ->sum('cout_livraison');

        return view('points_livreurs.montant_livreurs', compact(
            'livreurs',
            'livreursActifs',
            'livreursInactifs',
            'totalMontantMois'
        ));
    }

    public function situationFinanciere(Request $request, Utilisateur $livreur)
    {
        if ($livreur->role !== 'livreur') {
            abort(404);
        }

        if (!$livreur->statut_compte) {
            return redirect()->route('points-livreurs.montant-livreurs')
                ->with('error', 'Ce livreur est inactif.');
        }

        $dateDebut = $request->get('date_debut', Carbon::now()->startOfYear()->toDateString());
        $dateFin = $request->get('date_fin', Carbon::today()->toDateString());

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $commandesQuery = Commande::query()
            ->with(['client.boutique'])
            ->where('livreur_id', $livreur->id)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin);

        $nbColis = (int) (clone $commandesQuery)->count();

        $pointsQuery = PointsLivreur::query()
            ->where('utilisateur_id', $livreur->id)
            ->whereDate('date_commande', '>=', $dateDebut)
            ->whereDate('date_commande', '<=', $dateFin);

        $commandesParJour = (clone $commandesQuery)
            ->selectRaw('DATE(date_livraison) as jour, SUM(cout_global) as montant_global')
            ->groupBy('jour')
            ->pluck('montant_global', 'jour');

        $depensesParJour = (clone $pointsQuery)
            ->selectRaw('DATE(date_commande) as jour, SUM(depense) as depense')
            ->groupBy('jour')
            ->pluck('depense', 'jour');

        $montantsVersesParJour = (clone $pointsQuery)
            ->selectRaw('DATE(date_commande) as jour, SUM(montant_verse) as montant_verse')
            ->groupBy('jour')
            ->pluck('montant_verse', 'jour');

        $versementsAll = $commandesParJour->keys()
            ->merge($depensesParJour->keys())
            ->merge($montantsVersesParJour->keys())
            ->unique()
            ->sortDesc()
            ->map(function ($jour) use ($commandesParJour, $depensesParJour, $montantsVersesParJour) {
                $montantGlobal = (int) ($commandesParJour[$jour] ?? 0);
                $depense = (int) ($depensesParJour[$jour] ?? 0);
                $montantVerse = (int) ($montantsVersesParJour[$jour] ?? 0);
                $montantARemettre = max(0, $montantGlobal - $depense);
                $montantPaye = max(0, $montantVerse);
                $resteAPayerJour = max(0, $montantARemettre - $montantPaye);
                $estSolde = $resteAPayerJour <= 0;

                return [
                    'date' => $jour,
                    'montant_global' => $montantGlobal,
                    'montant_du' => $montantARemettre,
                    'montant_a_remettre' => $montantARemettre,
                    'montant_verse' => $montantPaye,
                    'reste_a_payer' => $resteAPayerJour,
                    'est_paye' => $estSolde,
                    'statut' => $estSolde ? 'Soldé' : 'Non Soldé',
                ];
            })
            ->values();

        $montantDu = (int) $versementsAll->sum('montant_a_remettre');
        $montantPaye = (int) $versementsAll->sum('montant_verse');
        $resteAPayer = max(0, $montantDu - $montantPaye);

        $page = (int) $request->get('page', 1);
        $perPage = 15;
        $versementsJournaliers = new LengthAwarePaginator(
            $versementsAll->forPage($page, $perPage)->values(),
            $versementsAll->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $nomComplet = trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? ''));

        return view('points_livreurs.situation_financiere', compact(
            'livreur',
            'nomComplet',
            'dateDebut',
            'dateFin',
            'montantDu',
            'montantPaye',
            'resteAPayer',
            'nbColis',
            'versementsJournaliers'
        ));
    }

    public function effectuerPaiementSituation(Request $request, Utilisateur $livreur)
    {
        if ($livreur->role !== 'livreur') {
            abort(404);
        }

        if (!$livreur->statut_compte) {
            return redirect()->route('points-livreurs.montant-livreurs')
                ->with('error', 'Ce livreur est inactif.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'montant' => 'required|integer|min:1',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        $resultat = $this->solderVersementJour($livreur->id, $date, (int) $validated['montant']);

        if (!$resultat['ok']) {
            return redirect()->back()->with('error', $resultat['message']);
        }

        $dateAffichee = Carbon::parse($date)->format('d/m/Y');
        $montantFmt = number_format($resultat['montant'], 0, ',', ' ');
        $resteFmt = number_format($resultat['reste'], 0, ',', ' ');

        $redirectParams = array_filter([
            'date_debut' => $validated['date_debut'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'page' => $validated['page'] ?? null,
        ]);

        return redirect()
            ->route('points-livreurs.situation-financiere', array_merge(['livreur' => $livreur->id], $redirectParams))
            ->with('success', "Versement du {$dateAffichee} : {$montantFmt} XOF enregistrés. Reste : {$resteFmt} XOF.");
    }

    public function effectuerPaiementSituationMasse(Request $request, Utilisateur $livreur)
    {
        if ($livreur->role !== 'livreur') {
            abort(404);
        }

        if (!$livreur->statut_compte) {
            return redirect()->route('points-livreurs.montant-livreurs')
                ->with('error', 'Ce livreur est inactif.');
        }

        $validated = $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $joursSoldes = 0;
        $montantTotal = 0;

        foreach (array_unique($validated['dates']) as $date) {
            $date = Carbon::parse($date)->toDateString();
            $resultat = $this->solderVersementJour($livreur->id, $date, null);

            if ($resultat['ok']) {
                $joursSoldes++;
                $montantTotal += $resultat['montant'];
            }
        }

        $redirectParams = array_filter([
            'date_debut' => $validated['date_debut'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'page' => $validated['page'] ?? null,
        ]);

        if ($joursSoldes === 0) {
            return redirect()
                ->route('points-livreurs.situation-financiere', array_merge(['livreur' => $livreur->id], $redirectParams))
                ->with('error', 'Aucun versement à solder dans la sélection.');
        }

        $montantFmt = number_format($montantTotal, 0, ',', ' ');

        return redirect()
            ->route('points-livreurs.situation-financiere', array_merge(['livreur' => $livreur->id], $redirectParams))
            ->with('success', "{$joursSoldes} jour(s) soldé(s) pour un total de {$montantFmt} XOF.");
    }

    private function solderVersementJour(int $livreurId, string $date, ?int $montantDemande): array
    {
        $point = $this->ensurePointRecetteForDay($livreurId, $date);

        $montantGlobal = (int) Commande::query()
            ->where('livreur_id', $livreurId)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', $date)
            ->sum('cout_global');

        $depense = (int) ($point->depense ?? 0);
        $montantVerse = (int) ($point->montant_verse ?? 0);
        $montantDu = max(0, $montantGlobal - $depense);
        $reste = max(0, $montantDu - $montantVerse);

        if ($reste <= 0) {
            return [
                'ok' => false,
                'montant' => 0,
                'reste' => 0,
                'message' => 'Ce versement est déjà entièrement enregistré.',
            ];
        }

        $montant = $montantDemande === null ? $reste : min($montantDemande, $reste);
        $point->montant_verse = $montantVerse + $montant;
        $point->save();

        return [
            'ok' => true,
            'montant' => $montant,
            'reste' => max(0, $montantDu - (int) $point->montant_verse),
            'message' => null,
        ];
    }

    private function ensurePointRecetteForDay(int $livreurId, string $date): PointsLivreur
    {
        PointsLivreur::consolidateDuplicatesForLivreurDay($livreurId, $date);

        $recette = (int) Commande::query()
            ->where('livreur_id', $livreurId)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', $date)
            ->sum('cout_livraison');

        $point = PointsLivreur::forLivreurAndDate($livreurId, $date);

        if ($point) {
            $point->recette = $recette;
            $point->recalculateGain();
            $point->save();

            return $point->fresh();
        }

        return PointsLivreur::create([
            'utilisateur_id' => $livreurId,
            'recette' => $recette,
            'depense' => 0,
            'montant_verse' => 0,
            'gain_jour' => $recette,
            'date_commande' => $date,
        ]);
    }

    public function listeMontants(Request $request)
    {
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $livreurId = $request->get('livreur_id');
        $perPage = $request->get('per_page', 50);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $statsBaseQuery = Commande::query();
        if ($livreurId) {
            $statsBaseQuery->where('livreur_id', $livreurId);
        }

        $statsMois = [
            'recus' => (clone $statsBaseQuery)->whereBetween('date_reception', [$startOfMonth, $endOfMonth])->count(),
            'livrees' => (clone $statsBaseQuery)->where('statut', 'Livré')->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])->count(),
            'non_livrees' => (clone $statsBaseQuery)->where('statut', 'Non Livré')->whereBetween('date_reception', [$startOfMonth, $endOfMonth])->count(),
            'retours' => (clone $statsBaseQuery)->where('statut', 'Retour')->whereBetween('date_retour', [$startOfMonth, $endOfMonth])->count(),
        ];

        if ($dateDebut && $dateFin && Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $query = Commande::query()
            ->join('utilisateurs as livreurs', 'livreurs.id', '=', 'commandes.livreur_id')
            ->where('commandes.statut', 'Livré')
            ->whereNotNull('commandes.date_livraison')
            ->selectRaw("commandes.livreur_id, livreurs.nom, livreurs.prenoms, DATE(commandes.date_livraison) as jour, SUM(commandes.cout_livraison) as montant")
            ->groupBy('commandes.livreur_id', 'livreurs.nom', 'livreurs.prenoms', 'jour');

        if ($dateDebut) {
            $query->whereDate('commandes.date_livraison', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('commandes.date_livraison', '<=', $dateFin);
        }

        if ($livreurId) {
            $query->where('commandes.livreur_id', $livreurId);
        }

        $rows = $query
            ->orderByDesc('jour')
            ->orderBy('livreurs.nom')
            ->paginate($perPage)
            ->withQueryString();

        $livreurs = Utilisateur::livreurs()->get();

        return view('points_livreurs.liste_montants', compact(
            'rows',
            'livreurs',
            'dateDebut',
            'dateFin',
            'livreurId',
            'statsMois'
        ));
    }

    public function printDepot(Request $request)
    {
        $utilisateurId = $request->get('utilisateur_id');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');

        $request->validate([
            'utilisateur_id' => 'required|integer',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $livreur = Utilisateur::findOrFail($utilisateurId);

        $commandesParJour = Commande::query()
            ->where('livreur_id', $utilisateurId)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin)
            ->selectRaw('DATE(date_livraison) as jour, SUM(cout_global) as montant_global')
            ->groupBy('jour')
            ->pluck('montant_global', 'jour');

        $depensesParJour = PointsLivreur::query()
            ->where('utilisateur_id', $utilisateurId)
            ->whereDate('date_commande', '>=', $dateDebut)
            ->whereDate('date_commande', '<=', $dateFin)
            ->selectRaw('DATE(date_commande) as jour, SUM(depense) as depense')
            ->groupBy('jour')
            ->pluck('depense', 'jour');

        $rows = [];
        $period = \Carbon\CarbonPeriod::create(Carbon::parse($dateDebut), Carbon::parse($dateFin));
        foreach ($period as $day) {
            $jour = $day->format('Y-m-d');
            $montantGlobal = (int) ($commandesParJour[$jour] ?? 0);
            $depense = (int) ($depensesParJour[$jour] ?? 0);
            $montantADeposer = $montantGlobal - $depense;

            if ($montantGlobal === 0 && $depense === 0) {
                continue;
            }

            $rows[] = [
                'date' => $jour,
                'montant_global' => $montantGlobal,
                'depense' => $depense,
                'montant_a_deposer' => $montantADeposer,
            ];
        }

        $totalDepot = collect($rows)->sum('montant_a_deposer');

        $pdf = Pdf::loadView('points_livreurs.print_depot', [
            'livreur' => $livreur,
            'rows' => $rows,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'totalDepot' => $totalDepot,
        ]);

        $fileName = 'Point_versements_' . Carbon::parse($dateDebut)->format('d-m-Y') . '_au_' . Carbon::parse($dateFin)->format('d-m-Y') . '_' . str_replace(' ', '_', trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? ''))) . '.pdf';

        return $pdf->stream($fileName);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|integer',
            'recette' => 'required|integer',
            'depense' => 'nullable|integer',
            'date_commande' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date_commande'])->toDateString();
        $validated['date_commande'] = $date;

        PointsLivreur::consolidateDuplicatesForLivreurDay((int) $validated['utilisateur_id'], $date);

        $existing = PointsLivreur::forLivreurAndDate((int) $validated['utilisateur_id'], $date);

        if ($existing) {
            $existing->recette = (int) ($existing->recette ?? 0) + (int) ($validated['recette'] ?? 0);
            $existing->depense = (int) ($existing->depense ?? 0) + (int) ($validated['depense'] ?? 0);
            $existing->recalculateGain();
            $existing->save();

            return redirect()->route('points-livreurs.index')
                ->with('success', 'Point mis à jour : les dépenses du jour ont été additionnées.');
        }

        $validated['gain_jour'] = (int) ($validated['recette'] ?? 0) - (int) ($validated['depense'] ?? 0);
        PointsLivreur::create($validated);

        return redirect()->route('points-livreurs.index')->with('success', 'Point enregistre avec succes');
    }

    public function syncRecettes(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'date' => 'nullable|date',
        ]);

        $dateDebut = $validated['date_debut']
            ?? $validated['date']
            ?? Carbon::today()->toDateString();
        $dateFin = $validated['date_fin']
            ?? $validated['date']
            ?? $dateDebut;

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $joursTraites = 0;
        $livreursTraites = 0;

        foreach (\Carbon\CarbonPeriod::create($dateDebut, $dateFin) as $day) {
            $date = $day->toDateString();
            $livreursTraites += $this->syncRecettesForDate($date);
            $joursTraites++;
        }

        $message = $dateDebut === $dateFin
            ? "Recettes synchronisées pour le {$dateDebut} ({$livreursTraites} livreur(s))."
            : "Recettes synchronisées du {$dateDebut} au {$dateFin} ({$joursTraites} jour(s), {$livreursTraites} point(s)).";

        return redirect()->back()->with('success', $message);
    }

    private function syncRecettesForDate(string $date): int
    {
        $commandesLivrees = Commande::query()
            ->whereDate('date_livraison', $date)
            ->where('statut', 'Livré')
            ->get()
            ->groupBy('livreur_id');

        $updated = 0;

        foreach ($commandesLivrees as $livreurId => $commandes) {
            if (!$livreurId) {
                continue;
            }

            $recette = (int) $commandes->sum('cout_livraison');

            PointsLivreur::consolidateDuplicatesForLivreurDay((int) $livreurId, $date);

            $pointLivreur = PointsLivreur::forLivreurAndDate((int) $livreurId, $date);

            if ($pointLivreur) {
                $pointLivreur->recette = $recette;
                $pointLivreur->gain_jour = $recette - ((int) ($pointLivreur->depense ?? 0));
                $pointLivreur->save();
            } else {
                PointsLivreur::create([
                    'utilisateur_id' => $livreurId,
                    'recette' => $recette,
                    'depense' => 0,
                    'gain_jour' => $recette,
                    'date_commande' => $date,
                ]);
            }

            $updated++;
        }

        return $updated;
    }

    public function show(PointsLivreur $pointsLivreur)
    {
        return response()->json($pointsLivreur);
    }

    public function update(Request $request, PointsLivreur $pointsLivreur)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|integer',
            'recette' => 'required|integer',
            'depense' => 'nullable|integer',
            'date_commande' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date_commande'])->toDateString();
        $validated['date_commande'] = $date;
        $validated['gain_jour'] = (int) ($validated['recette'] ?? 0) - (int) ($validated['depense'] ?? 0);

        PointsLivreur::consolidateDuplicatesForLivreurDay(
            (int) $validated['utilisateur_id'],
            $date
        );

        $existing = PointsLivreur::forLivreurAndDate(
            (int) $validated['utilisateur_id'],
            $date,
            $pointsLivreur->id
        );

        if ($existing) {
            $existing->recette = (int) ($existing->recette ?? 0) + (int) ($validated['recette'] ?? 0);
            $existing->depense = (int) ($existing->depense ?? 0) + (int) ($validated['depense'] ?? 0);
            $existing->recalculateGain();
            $existing->save();
            $pointsLivreur->delete();

            return redirect()->route('points-livreurs.index')
                ->with('success', 'Point fusionné avec l\'enregistrement existant pour ce livreur à cette date.');
        }

        $pointsLivreur->update($validated);

        return redirect()->route('points-livreurs.index')->with('success', 'Point modifie avec succes');
    }

    public function destroy(PointsLivreur $pointsLivreur)
    {
        $pointsLivreur->delete();
        return redirect()->route('points-livreurs.index')->with('success', 'Point supprime avec succes');
    }

    public function getByLivreur($livreurId)
    {
        $points = PointsLivreur::where('livreur_id', $livreurId)
            ->orderBy('date_points', 'desc')
            ->get();
        return response()->json($points);
    }

    public function getTotalByLivreur($livreurId)
    {
        $total = PointsLivreur::where('livreur_id', $livreurId)->sum('points');
        return response()->json(['livreur_id' => $livreurId, 'total_points' => $total]);
    }

    public function getClassement()
    {
        $classement = PointsLivreur::selectRaw('livreur_id, SUM(points) as total_points')
            ->groupBy('livreur_id')
            ->orderByDesc('total_points')
            ->get();
        return response()->json($classement);
    }
}

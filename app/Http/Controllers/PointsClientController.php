<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class PointsClientController extends Controller
{
    public function index(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::today()->format('Y-m-d'));
        $clientId = $request->get('client_id');

        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'client_id' => 'nullable|integer',
        ]);

        if ($dateDebut && $dateFin && Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $clients = Utilisateur::clients()->with('boutique')->get();

        $query = Commande::query()
            ->with(['client.boutique'])
            ->where('statut', 'Livré');

        if ($dateDebut) {
            $query->whereDate('date_livraison', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->whereDate('date_livraison', '<=', $dateFin);
        }

        if ($clientId) {
            $query->where('utilisateur_id', $clientId);
        }

        $moisRef = $dateFin ? Carbon::parse($dateFin) : Carbon::today();
        $moisDebut = $moisRef->copy()->startOfMonth()->format('Y-m-d');
        $moisFin = $moisRef->copy()->endOfMonth()->format('Y-m-d');

        $statsMoisQuery = Commande::query()
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', '>=', $moisDebut)
            ->whereDate('date_livraison', '<=', $moisFin);

        if ($clientId) {
            $statsMoisQuery->where('utilisateur_id', $clientId);
        }

        $montantGlobalMois = (int) (clone $statsMoisQuery)->sum('cout_global');
        $montantClientsMois = (int) (clone $statsMoisQuery)->sum('cout_reel');
        $gainMois = (int) (clone $statsMoisQuery)->sum('cout_livraison');
        $nbColisLivresMois = (int) (clone $statsMoisQuery)->count();

        $moisLabel = ucfirst($moisRef->copy()->locale('fr')->translatedFormat('F'));

        $rows = $query
            ->selectRaw('DATE(date_livraison) as jour, utilisateur_id, SUM(cout_global) as montant_global, SUM(cout_livraison) as montant_livraison, SUM(cout_reel) as montant_reel, COUNT(*) as nb_colis')
            ->groupBy('jour', 'utilisateur_id')
            ->orderBy('jour', 'desc')
            ->get()
            ->map(function ($row) {
                $row->jour = (string) $row->jour;
                return $row;
            });

        $totauxParJour = $rows
            ->groupBy('jour')
            ->map(function ($items) {
                return [
                    'montant_global' => (int) $items->sum('montant_global'),
                    'montant_livraison' => (int) $items->sum('montant_livraison'),
                    'montant_reel' => (int) $items->sum('montant_reel'),
                    'nb_colis' => (int) $items->sum('nb_colis'),
                ];
            });

        $totalGlobal = (int) $rows->sum('montant_reel');

        return view('points_clients.index', compact(
            'clients',
            'rows',
            'totauxParJour',
            'totalGlobal',
            'dateDebut',
            'dateFin',
            'clientId',
            'montantGlobalMois',
            'montantClientsMois',
            'gainMois',
            'nbColisLivresMois',
            'moisLabel'
        ));
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|integer',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        $clientId = $validated['client_id'];
        $dateDebut = $validated['date_debut'];
        $dateFin = $validated['date_fin'];

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $client = Utilisateur::with('boutique')->findOrFail($clientId);

        $rows = Commande::query()
            ->where('utilisateur_id', $clientId)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin)
            ->selectRaw('DATE(date_livraison) as jour, SUM(cout_reel) as montant_a_verser, COUNT(*) as nb_colis')
            ->groupBy('jour')
            ->orderBy('jour', 'asc')
            ->get();

        $totalMontant = (int) $rows->sum('montant_a_verser');
        $totalColis = (int) $rows->sum('nb_colis');

        $pdf = Pdf::loadView('points_clients.print', [
            'client' => $client,
            'rows' => $rows,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'totalMontant' => $totalMontant,
            'totalColis' => $totalColis,
        ]);

        $nom = $client->boutique->nom ?? trim(($client->nom ?? '') . ' ' . ($client->prenoms ?? ''));
        $fileName = 'Points_clients_' . Carbon::parse($dateDebut)->format('d-m-Y') . '_au_' . Carbon::parse($dateFin)->format('d-m-Y') . '_' . str_replace(' ', '_', $nom) . '.pdf';

        return $pdf->stream($fileName);
    }

    public function montantClients(Request $request)
    {
        $perPage = $request->integer('per_page', 20);
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $dateDebut = Carbon::now()->startOfYear()->toDateString();
        $dateFin = Carbon::today()->toDateString();

        $boutiques = Boutique::query()
            ->with('gerant')
            ->orderBy('nom')
            ->paginate($perPage)
            ->withQueryString();

        $boutiqueIds = $boutiques->pluck('id');
        $clientsParBoutique = Utilisateur::query()
            ->clients()
            ->whereIn('boutique_id', $boutiqueIds)
            ->get()
            ->groupBy('boutique_id');

        $clientIds = $clientsParBoutique->flatten()->pluck('id');

        $statsMois = Commande::query()
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])
            ->whereIn('utilisateur_id', $clientIds)
            ->selectRaw('utilisateur_id, SUM(cout_reel) as montant, COUNT(*) as nb_colis')
            ->groupBy('utilisateur_id')
            ->get()
            ->keyBy('utilisateur_id');

        $statsPeriode = Commande::query()
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin)
            ->whereIn('utilisateur_id', $clientIds)
            ->selectRaw('utilisateur_id, DATE(date_livraison) as jour, SUM(cout_reel) as montant_du, SUM(CASE WHEN COALESCE(paiement_effectue, 0) = 1 THEN cout_reel ELSE 0 END) as montant_paye')
            ->groupBy('utilisateur_id', 'jour')
            ->get()
            ->groupBy('utilisateur_id');

        $boutiques->getCollection()->transform(function ($boutique) use ($clientsParBoutique, $statsMois, $statsPeriode) {
            $clients = $clientsParBoutique->get($boutique->id, collect());
            $clientIdsBoutique = $clients->pluck('id');

            $montantMois = 0;
            $nbColisMois = 0;
            foreach ($clientIdsBoutique as $clientId) {
                $mois = $statsMois->get($clientId);
                $montantMois += (int) ($mois->montant ?? 0);
                $nbColisMois += (int) ($mois->nb_colis ?? 0);
            }

            $montantAPayer = 0;
            $montantPaye = 0;
            foreach ($clientIdsBoutique as $clientId) {
                foreach ($statsPeriode->get($clientId, collect()) as $row) {
                    $montantAPayer += (int) ($row->montant_du ?? 0);
                    $montantPaye += (int) ($row->montant_paye ?? 0);
                }
            }

            $boutique->nb_colis_mois = $nbColisMois;
            $boutique->montant_mois = $montantMois;
            $boutique->montant_a_payer = $montantAPayer;
            $boutique->montant_paye = $montantPaye;
            $boutique->reste_a_payer = max(0, $montantAPayer - $montantPaye);
            $boutique->a_client = $clients->isNotEmpty();

            return $boutique;
        });

        $boutiquesActives = (int) Boutique::query()->where('statut', true)->count();
        $boutiquesInactives = (int) Boutique::query()->where('statut', false)->count();
        $totalMontantMois = (int) Commande::query()
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])
            ->whereIn('utilisateur_id', Utilisateur::query()->clients()->pluck('id'))
            ->sum('cout_reel');

        return view('points_clients.montant_clients', compact(
            'boutiques',
            'boutiquesActives',
            'boutiquesInactives',
            'totalMontantMois'
        ));
    }

    public function situationFinanciere(Request $request, Boutique $boutique)
    {
        $clientIds = $this->clientIdsForBoutique($boutique);

        if (empty($clientIds)) {
            return redirect()->route('points-clients.montant-clients')
                ->with('error', 'Cette boutique n\'a pas de client associé.');
        }

        if (!$boutique->statut) {
            return redirect()->route('points-clients.montant-clients')
                ->with('error', 'Cette boutique est inactive.');
        }

        $dateDebut = $request->get('date_debut', Carbon::now()->startOfYear()->toDateString());
        $dateFin = $request->get('date_fin', Carbon::today()->toDateString());

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $commandesQuery = $this->commandesQueryForBoutique($clientIds, $dateDebut, $dateFin);
        $nbColis = (int) (clone $commandesQuery)->count();

        $paiementsParJour = (clone $commandesQuery)
            ->selectRaw('DATE(date_livraison) as jour')
            ->selectRaw('SUM(cout_reel) as montant_a_payer')
            ->selectRaw('SUM(CASE WHEN COALESCE(paiement_effectue, 0) = 1 THEN cout_reel ELSE 0 END) as montant_paye')
            ->selectRaw('MAX(date_paiement) as date_paiement')
            ->groupBy('jour')
            ->orderByDesc('jour')
            ->get();

        $paiementsAll = $paiementsParJour->map(function ($row) {
            $montantAPayer = (int) ($row->montant_a_payer ?? 0);
            $montantPaye = (int) ($row->montant_paye ?? 0);
            $resteAPayer = max(0, $montantAPayer - $montantPaye);
            $limiteAnnulation = $row->date_paiement
                ? Carbon::parse($row->date_paiement)->startOfDay()->addDays(3)
                : null;

            return [
                'date' => (string) $row->jour,
                'montant_a_payer' => $montantAPayer,
                'montant_paye' => $montantPaye,
                'reste_a_payer' => $resteAPayer,
                'statut' => $resteAPayer <= 0 ? 'Soldé' : 'Non Soldé',
                'peut_annuler' => $montantPaye > 0 && $limiteAnnulation && $limiteAnnulation->gt(Carbon::today()),
            ];
        })->values();

        $montantDu = (int) $paiementsAll->sum('montant_a_payer');
        $montantPaye = (int) $paiementsAll->sum('montant_paye');
        $resteAPayer = max(0, $montantDu - $montantPaye);

        $page = (int) $request->get('page', 1);
        $perPage = 15;
        $paiementsJournaliers = new LengthAwarePaginator(
            $paiementsAll->forPage($page, $perPage)->values(),
            $paiementsAll->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('points_clients.situation_financiere', compact(
            'boutique',
            'dateDebut',
            'dateFin',
            'montantDu',
            'montantPaye',
            'resteAPayer',
            'nbColis',
            'paiementsJournaliers'
        ));
    }

    public function effectuerPaiementSituation(Request $request, Boutique $boutique)
    {
        $clientIds = $this->clientIdsForBoutique($boutique);

        if (empty($clientIds) || !$boutique->statut) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'operateur' => 'required|string|max:100',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        $resultat = $this->solderPaiementClientJour($clientIds, $date, $validated['operateur']);

        if (!$resultat['ok']) {
            return redirect()->back()->with('error', $resultat['message']);
        }

        $dateAffichee = Carbon::parse($date)->format('d/m/Y');
        $montantFmt = number_format($resultat['montant'], 0, ',', ' ');

        $redirectParams = array_filter([
            'date_debut' => $validated['date_debut'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'page' => $validated['page'] ?? null,
        ]);

        return redirect()
            ->route('points-clients.situation-financiere', array_merge(['boutique' => $boutique->id], $redirectParams))
            ->with('success', "Paiement du {$dateAffichee} : {$montantFmt} XOF enregistrés.");
    }

    public function effectuerPaiementSituationMasse(Request $request, Boutique $boutique)
    {
        $clientIds = $this->clientIdsForBoutique($boutique);

        if (empty($clientIds) || !$boutique->statut) {
            abort(404);
        }

        $validated = $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
            'operateur' => 'required|string|max:100',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $joursSoldes = 0;
        $montantTotal = 0;

        foreach (array_unique($validated['dates']) as $date) {
            $date = Carbon::parse($date)->toDateString();
            $resultat = $this->solderPaiementClientJour($clientIds, $date, $validated['operateur']);

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
                ->route('points-clients.situation-financiere', array_merge(['boutique' => $boutique->id], $redirectParams))
                ->with('error', 'Aucun paiement à enregistrer dans la sélection.');
        }

        $montantFmt = number_format($montantTotal, 0, ',', ' ');

        return redirect()
            ->route('points-clients.situation-financiere', array_merge(['boutique' => $boutique->id], $redirectParams))
            ->with('success', "{$joursSoldes} jour(s) soldé(s) pour un total de {$montantFmt} XOF.");
    }

    public function annulerPaiementSituation(Request $request, Boutique $boutique)
    {
        $clientIds = $this->clientIdsForBoutique($boutique);

        if (empty($clientIds) || !$boutique->statut) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        $resultat = $this->annulerPaiementClientJour($clientIds, $date);

        $redirectParams = array_filter([
            'date_debut' => $validated['date_debut'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'page' => $validated['page'] ?? null,
        ]);

        if (!$resultat['ok']) {
            return redirect()
                ->route('points-clients.situation-financiere', array_merge(['boutique' => $boutique->id], $redirectParams))
                ->with('error', $resultat['message']);
        }

        $dateAffichee = Carbon::parse($date)->format('d/m/Y');
        $montantFmt = number_format($resultat['montant'], 0, ',', ' ');

        return redirect()
            ->route('points-clients.situation-financiere', array_merge(['boutique' => $boutique->id], $redirectParams))
            ->with('success', "Paiement du {$dateAffichee} annulé ({$montantFmt} XOF).");
    }

    private function clientIdsForBoutique(Boutique $boutique): array
    {
        return Utilisateur::query()
            ->clients()
            ->where('boutique_id', $boutique->id)
            ->pluck('id')
            ->all();
    }

    private function commandesQueryForBoutique(array $clientIds, string $dateDebut, string $dateFin)
    {
        return Commande::query()
            ->whereIn('utilisateur_id', $clientIds)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin);
    }

    private function solderPaiementClientJour(array $clientIds, string $date, string $operateur): array
    {
        $commandes = Commande::query()
            ->whereIn('utilisateur_id', $clientIds)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', $date)
            ->where(function ($query) {
                $query->where('paiement_effectue', false)
                    ->orWhereNull('paiement_effectue');
            })
            ->get();

        if ($commandes->isEmpty()) {
            return [
                'ok' => false,
                'montant' => 0,
                'message' => 'Ce jour est déjà entièrement payé.',
            ];
        }

        $montant = (int) $commandes->sum('cout_reel');

        Commande::query()
            ->whereIn('id', $commandes->pluck('id'))
            ->update([
                'paiement_effectue' => true,
                'operateur_paiement' => $operateur,
                'date_paiement' => now(),
            ]);

        return [
            'ok' => true,
            'montant' => $montant,
            'message' => null,
        ];
    }

    private function annulerPaiementClientJour(array $clientIds, string $date): array
    {
        $commandes = Commande::query()
            ->whereIn('utilisateur_id', $clientIds)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', $date)
            ->where('paiement_effectue', true)
            ->get();

        if ($commandes->isEmpty()) {
            return [
                'ok' => false,
                'montant' => 0,
                'message' => 'Aucun paiement à annuler pour ce jour.',
            ];
        }

        $datePaiement = $commandes->max('date_paiement');
        $limiteAnnulation = $datePaiement
            ? Carbon::parse($datePaiement)->startOfDay()->addDays(3)
            : null;

        if (!$limiteAnnulation || !$limiteAnnulation->gt(Carbon::today())) {
            return [
                'ok' => false,
                'montant' => 0,
                'message' => 'Le délai de 3 jours pour annuler ce paiement est dépassé.',
            ];
        }

        $montant = (int) $commandes->sum('cout_reel');

        Commande::query()
            ->whereIn('id', $commandes->pluck('id'))
            ->update([
                'paiement_effectue' => false,
                'operateur_paiement' => null,
                'date_paiement' => null,
            ]);

        return [
            'ok' => true,
            'montant' => $montant,
            'message' => null,
        ];
    }
}

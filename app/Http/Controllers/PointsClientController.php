<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
}

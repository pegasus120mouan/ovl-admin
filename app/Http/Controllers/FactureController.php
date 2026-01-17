<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\Utilisateur;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $clients = Utilisateur::clients()->get();

        $factures = Facture::with('client')
            ->orderByDesc('date_facture')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('factures.index', compact('clients', 'factures'));
    }

    private function nextNumeroFacture(Carbon $date): string
    {
        $year = $date->format('Y');
        $count = Facture::query()->whereYear('date_facture', $year)->count() + 1;
        return 'INV/' . $year . '/' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|integer',
            'mode' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'date_facture' => 'nullable|date',
        ]);

        $mode = $validated['mode'] ?? 'auto';
        if (!in_array($mode, ['auto', 'manuel'], true)) {
            $mode = 'auto';
        }

        $dateFacture = isset($validated['date_facture'])
            ? Carbon::parse($validated['date_facture'])
            : Carbon::today();

        $client = Utilisateur::findOrFail($validated['client_id']);

        if ($mode === 'manuel') {
            $facture = Facture::create([
                'numero' => $this->nextNumeroFacture($dateFacture),
                'client_id' => $client->id,
                'date_facture' => $dateFacture->toDateString(),
                'date_debut' => $validated['date_debut'] ?? null,
                'date_fin' => $validated['date_fin'] ?? null,
                'statut' => 'Brouillon',
                'total_ht' => 0,
                'total_ttc' => 0,
            ]);

            return redirect()->route('factures.show', $facture->id)->with('success', 'Facture créée (mode manuel). Ajoute les lignes.');
        }

        $dateDebut = isset($validated['date_debut']) ? Carbon::parse($validated['date_debut'])->toDateString() : null;
        $dateFin = isset($validated['date_fin']) ? Carbon::parse($validated['date_fin'])->toDateString() : null;

        if (!$dateDebut || !$dateFin) {
            return redirect()->back()->with('error', 'Veuillez renseigner une période (date début et date fin) pour la génération automatique.');
        }

        if (Carbon::parse($dateFin)->lt(Carbon::parse($dateDebut))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $commandes = Commande::query()
            ->where('utilisateur_id', $client->id)
            ->where('statut', 'Livré')
            ->whereNotNull('date_livraison')
            ->whereDate('date_livraison', '>=', $dateDebut)
            ->whereDate('date_livraison', '<=', $dateFin)
            ->orderBy('date_livraison')
            ->orderBy('id')
            ->get();

        if ($commandes->isEmpty()) {
            return redirect()->back()->with('error', 'Aucune commande livrée trouvée pour ce client sur la période sélectionnée.');
        }

        $facture = DB::transaction(function () use ($client, $dateFacture, $dateDebut, $dateFin, $commandes) {
            $numero = $this->nextNumeroFacture($dateFacture);

            $facture = Facture::create([
                'numero' => $numero,
                'client_id' => $client->id,
                'date_facture' => $dateFacture->toDateString(),
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'statut' => 'Brouillon',
                'total_ht' => 0,
                'total_ttc' => 0,
            ]);

            $total = 0;
            foreach ($commandes as $commande) {
                $prixUnitaire = (int) ($commande->cout_global ?? 0);
                $quantite = 1;
                $prixTotal = $quantite * $prixUnitaire;

                $designation = 'Prestation de livraison - Ref CMD' . $commande->id;
                if (!empty($commande->communes)) {
                    $designation .= ' - ' . $commande->communes;
                }
                if (!empty($commande->date_livraison)) {
                    $designation .= ' - Livree le ' . Carbon::parse($commande->date_livraison)->format('d/m/Y');
                }

                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'commande_id' => $commande->id,
                    'quantite' => $quantite,
                    'designation' => $designation,
                    'prix_unitaire' => $prixUnitaire,
                    'prix_total' => $prixTotal,
                ]);

                $total += $prixTotal;
            }

            $facture->total_ht = $total;
            $facture->total_ttc = $total;
            $facture->save();

            return $facture;
        });

        return redirect()->route('factures.show', $facture->id)->with('success', 'Facture générée avec succès.');
    }

    private function recalcTotaux(Facture $facture): void
    {
        $total = (int) $facture->lignes()->sum('prix_total');
        $facture->total_ht = $total;
        $facture->total_ttc = $total;
        $facture->save();
    }

    public function storeLigne(Request $request, Facture $facture)
    {
        $validated = $request->validate([
            'quantite' => 'required|integer|min:1',
            'designation' => 'required|string',
            'prix_unitaire' => 'required|integer|min:0',
        ]);

        $prixTotal = ((int) $validated['quantite']) * ((int) $validated['prix_unitaire']);

        FactureLigne::create([
            'facture_id' => $facture->id,
            'commande_id' => null,
            'quantite' => (int) $validated['quantite'],
            'designation' => $validated['designation'],
            'prix_unitaire' => (int) $validated['prix_unitaire'],
            'prix_total' => $prixTotal,
        ]);

        $this->recalcTotaux($facture);

        return redirect()->back()->with('success', 'Ligne ajoutée.');
    }

    public function updateLigne(Request $request, Facture $facture, FactureLigne $ligne)
    {
        if ($ligne->facture_id !== $facture->id) {
            abort(404);
        }

        $validated = $request->validate([
            'quantite' => 'required|integer|min:1',
            'designation' => 'required|string',
            'prix_unitaire' => 'required|integer|min:0',
        ]);

        $prixTotal = ((int) $validated['quantite']) * ((int) $validated['prix_unitaire']);

        $ligne->quantite = (int) $validated['quantite'];
        $ligne->designation = $validated['designation'];
        $ligne->prix_unitaire = (int) $validated['prix_unitaire'];
        $ligne->prix_total = $prixTotal;
        $ligne->save();

        $this->recalcTotaux($facture);

        return redirect()->back()->with('success', 'Ligne modifiée.');
    }

    public function destroyLigne(Facture $facture, FactureLigne $ligne)
    {
        if ($ligne->facture_id !== $facture->id) {
            abort(404);
        }

        $ligne->delete();
        $this->recalcTotaux($facture);

        return redirect()->back()->with('success', 'Ligne supprimée.');
    }

    public function show(Facture $facture)
    {
        $facture->load(['client', 'lignes']);
        return view('factures.show', compact('facture'));
    }

    public function updateStatut(Request $request, Facture $facture)
    {
        $validated = $request->validate([
            'statut' => 'required|string|in:Brouillon,Validé,Payé',
        ]);

        $current = $facture->statut ?? 'Brouillon';
        $next = $validated['statut'];

        $allowedTransitions = [
            'Brouillon' => ['Validé'],
            'Validé' => ['Payé'],
            'Payé' => [],
        ];

        if (!in_array($next, $allowedTransitions[$current] ?? [], true)) {
            abort(403);
        }

        $facture->statut = $next;
        $facture->save();

        return redirect()->back()->with('success', 'Statut de la facture mis à jour.');
    }

    public function print(Facture $facture)
    {
        $facture->load(['client', 'lignes']);

        $pdf = Pdf::loadView('factures.print', [
            'facture' => $facture,
        ]);

        $fileName = 'Facture_' . str_replace('/', '-', $facture->numero) . '.pdf';
        return $pdf->stream($fileName);
    }

    public function download(Facture $facture)
    {
        $facture->load(['client', 'lignes']);

        $pdf = Pdf::loadView('factures.print', [
            'facture' => $facture,
        ]);

        $fileName = 'Facture_' . str_replace('/', '-', $facture->numero) . '.pdf';
        return $pdf->download($fileName);
    }
}

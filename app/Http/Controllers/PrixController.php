<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Commune;
use App\Models\CoutLivraison;
use App\Models\Prix;
use App\Models\Zone;
use Illuminate\Http\Request;

class PrixController extends Controller
{
    public function indexWeb(Request $request, Commune $commune)
    {
        $perPage = $request->integer('per_page', 50);

        $selectedZoneId = $request->filled('zone_id')
            ? $request->integer('zone_id')
            : ($commune->zones()->orderBy('zones.nom_zone')->value('zones.zone_id') ?? $commune->commune_id);
        $zoneOrigine = Zone::find($selectedZoneId);

        $prixRows = Prix::query()
            ->with(['commune', 'zone'])
            ->when($selectedZoneId !== null, fn($q) => $q->where('zone_id', $selectedZoneId))
            ->orderBy('commune_id')
            ->paginate($perPage)
            ->withQueryString();

        $zones = Zone::query()->orderBy('nom_zone')->get();

        $destinations = Commune::query()
            ->when($selectedZoneId !== null, fn($q) => $q->whereHas('zones', fn($z) => $z->where('zones.zone_id', $selectedZoneId)))
            ->orderBy('nom_commune')
            ->get();

        $totalPrix = Prix::query()
            ->when($selectedZoneId !== null, fn($q) => $q->where('zone_id', $selectedZoneId))
            ->count();

        $coutLivraisons = CoutLivraison::query()->orderBy('cout_livraison')->get();

        return view('communes.prix', compact('commune', 'prixRows', 'zones', 'destinations', 'totalPrix', 'selectedZoneId', 'zoneOrigine', 'coutLivraisons'));
    }

    public function printWeb(Request $request, Commune $commune)
    {
        $selectedZoneId = $request->filled('zone_id')
            ? $request->integer('zone_id')
            : ($commune->zones()->orderBy('zones.nom_zone')->value('zones.zone_id') ?? $commune->commune_id);
        $zoneOrigine = Zone::find($selectedZoneId);

        $prixRows = Prix::query()
            ->with(['commune', 'zone'])
            ->where('zone_id', $selectedZoneId)
            ->orderBy('commune_id')
            ->get();

        $pdf = Pdf::loadView('communes.prix_print', compact('commune', 'prixRows', 'selectedZoneId', 'zoneOrigine'));

        $zoneName = $zoneOrigine?->nom_zone ?? ($commune->nom_commune ?? 'Zone');
        $fileName = 'Couts_livraisons_' . str_replace(' ', '_', $zoneName) . '.pdf';

        return $pdf->stream($fileName);
    }

    public function storeWeb(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|integer|exists:zones,zone_id',
            'commune_id' => 'required|integer|exists:communes,commune_id',
            'cout_livraison_id' => 'nullable|integer|exists:cout_livraison,id',
            'prix' => 'nullable|integer|min:0',
        ]);

        $zoneId = $validated['zone_id'] ?? $commune->commune_id;

        $montant = null;
        if (!empty($validated['cout_livraison_id'])) {
            $cout = CoutLivraison::find($validated['cout_livraison_id']);
            $montant = $cout?->cout_livraison;
        }
        if ($montant === null) {
            $montant = $validated['prix'] ?? null;
        }
        if ($montant === null) {
            return redirect()->route('communes.prix.index', $commune)->with('error', 'Veuillez sélectionner un prix');
        }

        Prix::create([
            'zone_id' => $zoneId,
            'commune_id' => $validated['commune_id'],
            'montant' => $montant,
        ]);

        return redirect()->route('communes.prix.index', [$commune, 'zone_id' => $zoneId])->with('success', 'Prix ajouté avec succès');
    }

    public function updateWeb(Request $request, Commune $commune, Prix $prix)
    {
        if ((int)$prix->zone_id !== (int)$commune->commune_id && !$request->filled('zone_id')) {
            return redirect()->route('communes.prix.index', $commune)->with('error', 'Prix introuvable pour cette commune');
        }

        $validated = $request->validate([
            'zone_id' => 'nullable|integer|exists:zones,zone_id',
            'commune_id' => 'required|integer|exists:communes,commune_id',
            'cout_livraison_id' => 'nullable|integer|exists:cout_livraison,id',
            'prix' => 'nullable|integer|min:0',
        ]);

        $zoneId = $validated['zone_id'] ?? $prix->zone_id;

        $montant = null;
        if (!empty($validated['cout_livraison_id'])) {
            $cout = CoutLivraison::find($validated['cout_livraison_id']);
            $montant = $cout?->cout_livraison;
        }
        if ($montant === null) {
            $montant = $validated['prix'] ?? null;
        }
        if ($montant === null) {
            return redirect()->route('communes.prix.index', $commune)->with('error', 'Veuillez sélectionner un prix');
        }

        $prix->update([
            'zone_id' => $zoneId,
            'commune_id' => $validated['commune_id'],
            'montant' => $montant,
        ]);

        return redirect()->route('communes.prix.index', [$commune, 'zone_id' => $zoneId])->with('success', 'Prix modifié avec succès');
    }

    public function destroyWeb(Commune $commune, Prix $prix)
    {
        if ((int)$prix->zone_id !== (int)$commune->commune_id) {
            return redirect()->route('communes.prix.index', $commune)->with('error', 'Prix introuvable pour cette commune');
        }

        $prix->delete();

        return redirect()->route('communes.prix.index', [$commune, 'zone_id' => $prix->zone_id])->with('success', 'Prix supprimé avec succès');
    }

    public function index()
    {
        $prix = Prix::with(['commune', 'zone'])->get();
        return response()->json($prix);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commune_id' => 'required|exists:communes,commune_id',
            'zone_id' => 'required|exists:zones,zone_id',
            'prix' => 'required|integer',
        ]);

        $prix = Prix::create([
            'commune_id' => $validated['commune_id'],
            'zone_id' => $validated['zone_id'],
            'montant' => $validated['prix'],
        ]);
        return response()->json($prix->load(['commune', 'zone']), 201);
    }

    public function show(Prix $prix)
    {
        return response()->json($prix->load(['commune', 'zone']));
    }

    public function update(Request $request, Prix $prix)
    {
        $validated = $request->validate([
            'commune_id' => 'sometimes|required|exists:communes,commune_id',
            'zone_id' => 'sometimes|required|exists:zones,zone_id',
            'prix' => 'sometimes|required|integer',
        ]);

        $update = [];
        if (array_key_exists('commune_id', $validated)) {
            $update['commune_id'] = $validated['commune_id'];
        }
        if (array_key_exists('zone_id', $validated)) {
            $update['zone_id'] = $validated['zone_id'];
        }
        if (array_key_exists('prix', $validated)) {
            $update['montant'] = $validated['prix'];
        }

        $prix->update($update);
        return response()->json($prix->load(['commune', 'zone']));
    }

    public function destroy(Prix $prix)
    {
        $prix->delete();
        return response()->json(null, 204);
    }

    public function getPrixByCommune($communeId)
    {
        $prix = Prix::where('commune_id', $communeId)->with(['commune', 'zone'])->get();
        return response()->json($prix);
    }

    public function getPrixByZone($zoneId)
    {
        $prix = Prix::where('zone_id', $zoneId)->with(['commune', 'zone'])->get();
        return response()->json($prix);
    }

    public function getPrixByCommuneAndZone($communeId, $zoneId)
    {
        $prix = Prix::where('commune_id', $communeId)
            ->where('zone_id', $zoneId)
            ->with(['commune', 'zone'])
            ->first();
        return response()->json($prix);
    }
}

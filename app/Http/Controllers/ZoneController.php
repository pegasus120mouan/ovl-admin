<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Prix;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function indexWeb(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $zones = Zone::query()
            ->orderBy('nom_zone')
            ->paginate($perPage)
            ->withQueryString();

        $totalZones = Zone::count();

        return view('communes.zones', compact('zones', 'totalZones'));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'nom_zone' => 'required|string|max:255',
        ]);

        Zone::create($validated);

        return redirect()->route('communes.zones')->with('success', 'Zone ajoutée avec succès');
    }

    public function updateWeb(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'nom_zone' => 'required|string|max:255',
        ]);

        $zone->update($validated);

        return redirect()->route('communes.zones')->with('success', 'Zone modifiée avec succès');
    }

    public function destroyWeb(Zone $zone)
    {
        $zone->delete();

        return redirect()->route('communes.zones')->with('success', 'Zone supprimée avec succès');
    }

    public function prixIndexWeb(Request $request, Zone $zone)
    {
        $perPage = $request->integer('per_page', 50);

        $communeIds = $zone->communes()->pluck('communes.commune_id');

        $prixRows = Prix::query()
            ->with(['commune', 'zone'])
            ->where('zone_id', $zone->zone_id)
            ->when($communeIds->isNotEmpty(), fn($q) => $q->whereIn('commune_id', $communeIds))
            ->orderBy('commune_id')
            ->paginate($perPage)
            ->withQueryString();

        $communesAssociees = $zone->communes()->orderBy('nom_commune')->get();

        $communesDisponibles = Commune::query()
            ->whereDoesntHave('zones', fn($z) => $z->where('zones.zone_id', $zone->zone_id))
            ->orderBy('nom_commune')
            ->get();

        $totalPrix = Prix::query()->where('zone_id', $zone->zone_id)->count();

        return view('communes.zones_prix', compact('zone', 'prixRows', 'communesAssociees', 'communesDisponibles', 'totalPrix'));
    }

    public function attachCommuneWeb(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'commune_id' => 'required|integer|exists:communes,commune_id',
        ]);

        $zone->communes()->syncWithoutDetaching([$validated['commune_id']]);

        return redirect()->route('communes.zones.prix.index', $zone)->with('success', 'Commune associée à la zone');
    }

    public function detachCommuneWeb(Zone $zone, Commune $commune)
    {
        $zone->communes()->detach($commune->commune_id);

        return redirect()->route('communes.zones.prix.index', $zone)->with('success', 'Commune retirée de la zone');
    }

    public function prixStoreWeb(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'commune_id' => 'required|integer|exists:communes,commune_id',
            'prix' => 'required|integer|min:0',
        ]);

        if (!$zone->communes()->where('communes.commune_id', $validated['commune_id'])->exists()) {
            return redirect()->route('communes.zones.prix.index', $zone)->with('error', 'La commune n\'est pas associée à cette zone');
        }

        Prix::create([
            'zone_id' => $zone->zone_id,
            'commune_id' => $validated['commune_id'],
            'montant' => $validated['prix'],
        ]);

        return redirect()->route('communes.zones.prix.index', $zone)->with('success', 'Prix ajouté avec succès');
    }

    public function prixUpdateWeb(Request $request, Zone $zone, Prix $prix)
    {
        if ((int)$prix->zone_id !== (int)$zone->zone_id) {
            return redirect()->route('communes.zones.prix.index', $zone)->with('error', 'Prix introuvable pour cette zone');
        }

        $validated = $request->validate([
            'commune_id' => 'required|integer|exists:communes,commune_id',
            'prix' => 'required|integer|min:0',
        ]);

        if (!$zone->communes()->where('communes.commune_id', $validated['commune_id'])->exists()) {
            return redirect()->route('communes.zones.prix.index', $zone)->with('error', 'La commune n\'est pas associée à cette zone');
        }

        $prix->update([
            'commune_id' => $validated['commune_id'],
            'montant' => $validated['prix'],
        ]);

        return redirect()->route('communes.zones.prix.index', $zone)->with('success', 'Prix modifié avec succès');
    }

    public function prixDestroyWeb(Zone $zone, Prix $prix)
    {
        if ((int)$prix->zone_id !== (int)$zone->zone_id) {
            return redirect()->route('communes.zones.prix.index', $zone)->with('error', 'Prix introuvable pour cette zone');
        }

        $prix->delete();

        return redirect()->route('communes.zones.prix.index', $zone)->with('success', 'Prix supprimé avec succès');
    }

    public function index()
    {
        $zones = Zone::with('communes')->get();
        return response()->json($zones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_zone' => 'required|string|max:255',
        ]);

        $zone = Zone::create($validated);
        return response()->json($zone, 201);
    }

    public function show(Zone $zone)
    {
        return response()->json($zone->load('communes'));
    }

    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'nom_zone' => 'sometimes|required|string|max:255',
        ]);

        $zone->update($validated);
        return response()->json($zone);
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->json(null, 204);
    }

    public function getCommunes(Zone $zone)
    {
        return response()->json($zone->communes);
    }

    public function getPrix(Zone $zone)
    {
        return response()->json($zone->prix);
    }
}

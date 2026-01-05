<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use Illuminate\Http\Request;

class CommuneController extends Controller
{
    public function indexWeb(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $communes = Commune::query()
            ->orderBy('nom_commune')
            ->paginate($perPage)
            ->withQueryString();

        $totalCommunes = Commune::count();

        return view('communes.index', compact('communes', 'totalCommunes'));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'nom_commune' => 'required|string|max:255',
        ]);

        Commune::create($validated);

        return redirect()->route('communes.index')->with('success', 'Commune ajoutée avec succès');
    }

    public function updateWeb(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'nom_commune' => 'required|string|max:255',
        ]);

        $commune->update($validated);

        return redirect()->route('communes.index')->with('success', 'Commune modifiée avec succès');
    }

    public function destroyWeb(Commune $commune)
    {
        $commune->delete();

        return redirect()->route('communes.index')->with('success', 'Commune supprimée avec succès');
    }

    public function index()
    {
        $communes = Commune::with('zones')->get();
        return response()->json($communes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_commune' => 'required|string|max:255',
        ]);

        $commune = Commune::create($validated);
        return response()->json($commune, 201);
    }

    public function show(Commune $commune)
    {
        return response()->json($commune->load('zones'));
    }

    public function update(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'nom_commune' => 'sometimes|required|string|max:255',
        ]);

        $commune->update($validated);
        return response()->json($commune);
    }

    public function destroy(Commune $commune)
    {
        $commune->delete();
        return response()->json(null, 204);
    }

    public function attachZone(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,zone_id',
        ]);

        $commune->zones()->attach($validated['zone_id']);
        return response()->json($commune->load('zones'));
    }

    public function detachZone(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,zone_id',
        ]);

        $commune->zones()->detach($validated['zone_id']);
        return response()->json($commune->load('zones'));
    }

    public function getPrix(Commune $commune)
    {
        return response()->json($commune->prix);
    }
}

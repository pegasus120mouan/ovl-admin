<?php

namespace App\Http\Controllers;

use App\Models\Prix;
use Illuminate\Http\Request;

class PrixController extends Controller
{
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

        $prix = Prix::create($validated);
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

        $prix->update($validated);
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

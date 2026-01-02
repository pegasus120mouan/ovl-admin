<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
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

<?php

namespace App\Http\Controllers;

use App\Models\CompteStatut;
use Illuminate\Http\Request;

class CompteStatutController extends Controller
{
    public function index()
    {
        $statuts = CompteStatut::all();
        return response()->json($statuts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'statut' => 'required|string|max:255',
        ]);

        $statut = CompteStatut::create($validated);
        return response()->json($statut, 201);
    }

    public function show(CompteStatut $compteStatut)
    {
        return response()->json($compteStatut);
    }

    public function update(Request $request, CompteStatut $compteStatut)
    {
        $validated = $request->validate([
            'statut' => 'sometimes|required|string|max:255',
        ]);

        $compteStatut->update($validated);
        return response()->json($compteStatut);
    }

    public function destroy(CompteStatut $compteStatut)
    {
        $compteStatut->delete();
        return response()->json(null, 204);
    }
}

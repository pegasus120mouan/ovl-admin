<?php

namespace App\Http\Controllers;

use App\Models\StatutLivraison;
use Illuminate\Http\Request;

class StatutLivraisonController extends Controller
{
    public function index()
    {
        $statuts = StatutLivraison::all();
        return response()->json($statuts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'statut' => 'required|string|max:255',
        ]);

        $statut = StatutLivraison::create($validated);
        return response()->json($statut, 201);
    }

    public function show(StatutLivraison $statutLivraison)
    {
        return response()->json($statutLivraison);
    }

    public function update(Request $request, StatutLivraison $statutLivraison)
    {
        $validated = $request->validate([
            'statut' => 'sometimes|required|string|max:255',
        ]);

        $statutLivraison->update($validated);
        return response()->json($statutLivraison);
    }

    public function destroy(StatutLivraison $statutLivraison)
    {
        $statutLivraison->delete();
        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TypePaiement;
use Illuminate\Http\Request;

class TypePaiementController extends Controller
{
    public function index()
    {
        $types = TypePaiement::all();
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operateur' => 'required|string|max:255',
            'logo' => 'nullable|string|max:255',
        ]);

        $type = TypePaiement::create($validated);
        return response()->json($type, 201);
    }

    public function show(TypePaiement $typePaiement)
    {
        return response()->json($typePaiement);
    }

    public function update(Request $request, TypePaiement $typePaiement)
    {
        $validated = $request->validate([
            'operateur' => 'sometimes|required|string|max:255',
            'logo' => 'nullable|string|max:255',
        ]);

        $typePaiement->update($validated);
        return response()->json($typePaiement);
    }

    public function destroy(TypePaiement $typePaiement)
    {
        $typePaiement->delete();
        return response()->json(null, 204);
    }
}

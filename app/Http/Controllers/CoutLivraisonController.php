<?php

namespace App\Http\Controllers;

use App\Models\CoutLivraison;
use Illuminate\Http\Request;

class CoutLivraisonController extends Controller
{
    public function index()
    {
        $couts = CoutLivraison::all();
        return response()->json($couts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zone' => 'required|string|max:255',
            'cout' => 'required|integer',
        ]);

        $cout = CoutLivraison::create($validated);
        return response()->json($cout, 201);
    }

    public function show(CoutLivraison $coutLivraison)
    {
        return response()->json($coutLivraison);
    }

    public function update(Request $request, CoutLivraison $coutLivraison)
    {
        $validated = $request->validate([
            'zone' => 'sometimes|required|string|max:255',
            'cout' => 'sometimes|required|integer',
        ]);

        $coutLivraison->update($validated);
        return response()->json($coutLivraison);
    }

    public function destroy(CoutLivraison $coutLivraison)
    {
        $coutLivraison->delete();
        return response()->json(null, 204);
    }

    public function getByZone($zone)
    {
        $cout = CoutLivraison::where('zone', $zone)->first();
        return response()->json($cout);
    }
}

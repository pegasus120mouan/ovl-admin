<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use Illuminate\Http\Request;

class DetteController extends Controller
{
    public function index()
    {
        $dettes = Dette::with('versements')->get();
        return response()->json($dettes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_debiteur' => 'required|string|max:255',
            'montant_initial' => 'required|numeric',
            'montant_actuel' => 'required|numeric',
            'montants_payes' => 'nullable|numeric',
            'reste' => 'nullable|numeric',
            'date_dette' => 'required|date',
            'date_echeance' => 'nullable|date',
            'statut' => 'nullable|string|max:255',
        ]);

        $validated['reste'] = $validated['montant_actuel'] - ($validated['montants_payes'] ?? 0);

        $dette = Dette::create($validated);
        return response()->json($dette, 201);
    }

    public function show(Dette $dette)
    {
        return response()->json($dette->load('versements'));
    }

    public function update(Request $request, Dette $dette)
    {
        $validated = $request->validate([
            'nom_debiteur' => 'sometimes|required|string|max:255',
            'montant_initial' => 'sometimes|required|numeric',
            'montant_actuel' => 'sometimes|required|numeric',
            'montants_payes' => 'nullable|numeric',
            'reste' => 'nullable|numeric',
            'date_dette' => 'sometimes|required|date',
            'date_echeance' => 'nullable|date',
            'statut' => 'nullable|string|max:255',
        ]);

        $dette->update($validated);
        return response()->json($dette);
    }

    public function destroy(Dette $dette)
    {
        $dette->delete();
        return response()->json(null, 204);
    }

    public function getEnCours()
    {
        $dettes = Dette::enCours()->with('versements')->get();
        return response()->json($dettes);
    }

    public function getSoldees()
    {
        $dettes = Dette::solde()->with('versements')->get();
        return response()->json($dettes);
    }

    public function getVersements(Dette $dette)
    {
        return response()->json($dette->versements);
    }

    public function getStatistiques()
    {
        $stats = [
            'total_dettes' => Dette::count(),
            'dettes_en_cours' => Dette::enCours()->count(),
            'dettes_soldees' => Dette::solde()->count(),
            'montant_total_initial' => Dette::sum('montant_initial'),
            'montant_total_paye' => Dette::sum('montants_payes'),
            'montant_total_reste' => Dette::sum('reste'),
        ];
        return response()->json($stats);
    }
}

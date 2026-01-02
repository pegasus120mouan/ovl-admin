<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index()
    {
        $contrats = Contrat::with('engin')->get();
        return response()->json($contrats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_engin' => 'required|exists:engins,engin_id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'montant' => 'required|numeric',
            'statut' => 'nullable|string|max:255',
        ]);

        $contrat = Contrat::create($validated);
        return response()->json($contrat->load('engin'), 201);
    }

    public function show(Contrat $contrat)
    {
        return response()->json($contrat->load('engin'));
    }

    public function update(Request $request, Contrat $contrat)
    {
        $validated = $request->validate([
            'id_engin' => 'sometimes|required|exists:engins,engin_id',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date',
            'montant' => 'sometimes|required|numeric',
            'statut' => 'nullable|string|max:255',
        ]);

        $contrat->update($validated);
        return response()->json($contrat->load('engin'));
    }

    public function destroy(Contrat $contrat)
    {
        $contrat->delete();
        return response()->json(null, 204);
    }

    public function getByEngin($enginId)
    {
        $contrats = Contrat::where('id_engin', $enginId)->with('engin')->get();
        return response()->json($contrats);
    }

    public function getActifs()
    {
        $contrats = Contrat::where('date_fin', '>=', now()->toDateString())
            ->with('engin')
            ->get();
        return response()->json($contrats);
    }

    public function getExpires()
    {
        $contrats = Contrat::where('date_fin', '<', now()->toDateString())
            ->with('engin')
            ->get();
        return response()->json($contrats);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Versement;
use Illuminate\Http\Request;

class VersementController extends Controller
{
    public function index()
    {
        $versements = Versement::with('dette')->get();
        return response()->json($versements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dette_id' => 'required|exists:dette,id',
            'montant_versement' => 'required|integer',
            'date_versement' => 'required|date',
        ]);

        $versement = Versement::create($validated);
        return response()->json($versement->load('dette'), 201);
    }

    public function show(Versement $versement)
    {
        return response()->json($versement->load('dette'));
    }

    public function update(Request $request, Versement $versement)
    {
        $validated = $request->validate([
            'dette_id' => 'sometimes|required|exists:dette,id',
            'montant_versement' => 'sometimes|required|integer',
            'date_versement' => 'sometimes|required|date',
        ]);

        $versement->update($validated);
        return response()->json($versement->load('dette'));
    }

    public function destroy(Versement $versement)
    {
        $versement->delete();
        return response()->json(null, 204);
    }

    public function getByDette($detteId)
    {
        $versements = Versement::where('dette_id', $detteId)
            ->with('dette')
            ->orderBy('date_versement', 'desc')
            ->get();
        return response()->json($versements);
    }

    public function getByDate($date)
    {
        $versements = Versement::whereDate('date_versement', $date)
            ->with('dette')
            ->get();
        return response()->json($versements);
    }

    public function getByPeriode(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        $versements = Versement::whereBetween('date_versement', [$validated['date_debut'], $validated['date_fin']])
            ->with('dette')
            ->get();
        return response()->json($versements);
    }
}

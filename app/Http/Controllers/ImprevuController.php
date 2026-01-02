<?php

namespace App\Http\Controllers;

use App\Models\Imprevu;
use Illuminate\Http\Request;

class ImprevuController extends Controller
{
    public function index()
    {
        $imprevus = Imprevu::with('livreur')->get();
        return response()->json($imprevus);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'livreur_id' => 'required|exists:utilisateurs,id',
            'description' => 'required|string',
            'montant' => 'required|integer',
            'date_imprevu' => 'required|date',
        ]);

        $imprevu = Imprevu::create($validated);
        return response()->json($imprevu->load('livreur'), 201);
    }

    public function show(Imprevu $imprevu)
    {
        return response()->json($imprevu->load('livreur'));
    }

    public function update(Request $request, Imprevu $imprevu)
    {
        $validated = $request->validate([
            'livreur_id' => 'sometimes|required|exists:utilisateurs,id',
            'description' => 'sometimes|required|string',
            'montant' => 'sometimes|required|integer',
            'date_imprevu' => 'sometimes|required|date',
        ]);

        $imprevu->update($validated);
        return response()->json($imprevu->load('livreur'));
    }

    public function destroy(Imprevu $imprevu)
    {
        $imprevu->delete();
        return response()->json(null, 204);
    }

    public function getByLivreur($livreurId)
    {
        $imprevus = Imprevu::where('livreur_id', $livreurId)
            ->with('livreur')
            ->orderBy('date_imprevu', 'desc')
            ->get();
        return response()->json($imprevus);
    }

    public function getByDate($date)
    {
        $imprevus = Imprevu::whereDate('date_imprevu', $date)
            ->with('livreur')
            ->get();
        return response()->json($imprevus);
    }

    public function getByPeriode(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        $imprevus = Imprevu::whereBetween('date_imprevu', [$validated['date_debut'], $validated['date_fin']])
            ->with('livreur')
            ->get();
        return response()->json($imprevus);
    }

    public function getStatistiques()
    {
        $stats = [
            'total_imprevus' => Imprevu::count(),
            'montant_total' => Imprevu::sum('montant'),
        ];
        return response()->json($stats);
    }
}

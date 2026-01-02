<?php

namespace App\Http\Controllers;

use App\Models\TotalCoutParJour;
use Illuminate\Http\Request;

class TotalCoutParJourController extends Controller
{
    public function index()
    {
        $totaux = TotalCoutParJour::with('typePaiement')->get();
        return response()->json($totaux);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'boutique' => 'required|string|max:255',
            'date_cout' => 'required|date',
            'total_cout' => 'required|numeric',
            'type_paiement_id' => 'nullable|exists:type_paiement,id',
            'statut_paiement' => 'nullable|string|max:255',
        ]);

        $total = TotalCoutParJour::create($validated);
        return response()->json($total->load('typePaiement'), 201);
    }

    public function show(TotalCoutParJour $totalCoutParJour)
    {
        return response()->json($totalCoutParJour->load('typePaiement'));
    }

    public function update(Request $request, TotalCoutParJour $totalCoutParJour)
    {
        $validated = $request->validate([
            'boutique' => 'sometimes|required|string|max:255',
            'date_cout' => 'sometimes|required|date',
            'total_cout' => 'sometimes|required|numeric',
            'type_paiement_id' => 'nullable|exists:type_paiement,id',
            'statut_paiement' => 'nullable|string|max:255',
        ]);

        $totalCoutParJour->update($validated);
        return response()->json($totalCoutParJour->load('typePaiement'));
    }

    public function destroy(TotalCoutParJour $totalCoutParJour)
    {
        $totalCoutParJour->delete();
        return response()->json(null, 204);
    }

    public function getByBoutique($boutique)
    {
        $totaux = TotalCoutParJour::where('boutique', $boutique)
            ->with('typePaiement')
            ->orderBy('date_cout', 'desc')
            ->get();
        return response()->json($totaux);
    }

    public function getByDate($date)
    {
        $totaux = TotalCoutParJour::whereDate('date_cout', $date)
            ->with('typePaiement')
            ->get();
        return response()->json($totaux);
    }

    public function getByPeriode(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
        ]);

        $totaux = TotalCoutParJour::whereBetween('date_cout', [$validated['date_debut'], $validated['date_fin']])
            ->with('typePaiement')
            ->get();
        return response()->json($totaux);
    }

    public function getPayes()
    {
        $totaux = TotalCoutParJour::paye()->with('typePaiement')->get();
        return response()->json($totaux);
    }

    public function getNonPayes()
    {
        $totaux = TotalCoutParJour::nonPaye()->with('typePaiement')->get();
        return response()->json($totaux);
    }

    public function marquerPaye(Request $request, TotalCoutParJour $totalCoutParJour)
    {
        $validated = $request->validate([
            'type_paiement_id' => 'required|exists:type_paiement,id',
        ]);

        $totalCoutParJour->update([
            'statut_paiement' => 'Payé',
            'type_paiement_id' => $validated['type_paiement_id'],
        ]);
        return response()->json($totalCoutParJour->load('typePaiement'));
    }

    public function getStatistiques()
    {
        $stats = [
            'total_enregistrements' => TotalCoutParJour::count(),
            'total_payes' => TotalCoutParJour::paye()->count(),
            'total_non_payes' => TotalCoutParJour::nonPaye()->count(),
            'montant_total' => TotalCoutParJour::sum('total_cout'),
            'montant_paye' => TotalCoutParJour::paye()->sum('total_cout'),
            'montant_non_paye' => TotalCoutParJour::nonPaye()->sum('total_cout'),
        ];
        return response()->json($stats);
    }

    public function getStatistiquesByBoutique($boutique)
    {
        $stats = [
            'boutique' => $boutique,
            'total_enregistrements' => TotalCoutParJour::where('boutique', $boutique)->count(),
            'total_payes' => TotalCoutParJour::where('boutique', $boutique)->paye()->count(),
            'total_non_payes' => TotalCoutParJour::where('boutique', $boutique)->nonPaye()->count(),
            'montant_total' => TotalCoutParJour::where('boutique', $boutique)->sum('total_cout'),
            'montant_paye' => TotalCoutParJour::where('boutique', $boutique)->paye()->sum('total_cout'),
            'montant_non_paye' => TotalCoutParJour::where('boutique', $boutique)->nonPaye()->sum('total_cout'),
        ];
        return response()->json($stats);
    }
}

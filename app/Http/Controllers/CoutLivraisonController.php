<?php

namespace App\Http\Controllers;

use App\Models\CoutLivraison;
use Illuminate\Http\Request;

class CoutLivraisonController extends Controller
{
    public function indexWeb(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $couts = CoutLivraison::query()
            ->orderBy('cout_livraison')
            ->paginate($perPage)
            ->withQueryString();

        $totalCouts = CoutLivraison::count();
        $coutMin = CoutLivraison::min('cout_livraison');
        $coutMax = CoutLivraison::max('cout_livraison');
        $coutMoyen = CoutLivraison::avg('cout_livraison');

        return view('cout_livraisons.index', compact('couts', 'totalCouts', 'coutMin', 'coutMax', 'coutMoyen'));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'cout_livraison' => 'required|integer|min:0',
        ]);

        CoutLivraison::create($validated);

        return redirect()->route('cout-livraisons.index')->with('success', 'Coût de livraison ajouté avec succès');
    }

    public function updateWeb(Request $request, CoutLivraison $coutLivraison)
    {
        $validated = $request->validate([
            'cout_livraison' => 'required|integer|min:0',
        ]);

        $coutLivraison->update($validated);

        return redirect()->route('cout-livraisons.index')->with('success', 'Coût de livraison modifié avec succès');
    }

    public function destroyWeb(CoutLivraison $coutLivraison)
    {
        $coutLivraison->delete();

        return redirect()->route('cout-livraisons.index')->with('success', 'Coût de livraison supprimé avec succès');
    }

    public function index()
    {
        $couts = CoutLivraison::all();
        return response()->json($couts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cout_livraison' => 'required|integer|min:0',
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
            'cout_livraison' => 'sometimes|required|integer|min:0',
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
        return response()->json(null, 404);
    }
}

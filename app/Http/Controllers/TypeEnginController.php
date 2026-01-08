<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\TypeEngin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TypeEnginController extends Controller
{
    public function indexWeb(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $types = TypeEngin::query()
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $totalTypes = TypeEngin::count();

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $colisRecusMois = Commande::query()
            ->whereBetween('date_reception', [$startOfMonth, $endOfMonth])
            ->count();

        $colisLivresMois = Commande::query()
            ->livre()
            ->whereBetween('date_livraison', [$startOfMonth, $endOfMonth])
            ->count();

        $colisNonLivresMois = Commande::query()
            ->nonLivre()
            ->whereBetween('date_reception', [$startOfMonth, $endOfMonth])
            ->count();

        $colisRetoursMois = Commande::query()
            ->retour()
            ->whereBetween('date_retour', [$startOfMonth, $endOfMonth])
            ->count();

        return view('engins.type_engins', compact(
            'types',
            'totalTypes',
            'colisRecusMois',
            'colisLivresMois',
            'colisNonLivresMois',
            'colisRetoursMois'
        ));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        TypeEngin::create($validated);

        return redirect()->route('engins.type_engins')->with('success', "Type d'engin ajouté avec succès");
    }

    public function updateWeb(Request $request, TypeEngin $typeEngin)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        $typeEngin->update($validated);

        return redirect()->route('engins.type_engins')->with('success', "Type d'engin modifié avec succès");
    }

    public function destroyWeb(TypeEngin $typeEngin)
    {
        if ($typeEngin->engins()->exists()) {
            return redirect()->route('engins.type_engins')->with('error', "Impossible de supprimer: des engins utilisent déjà ce type");
        }

        $typeEngin->delete();

        return redirect()->route('engins.type_engins')->with('success', "Type d'engin supprimé avec succès");
    }

    public function index()
    {
        $types = TypeEngin::all();
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        $type = TypeEngin::create($validated);
        return response()->json($type, 201);
    }

    public function show(TypeEngin $typeEngin)
    {
        return response()->json($typeEngin->load('engins'));
    }

    public function update(Request $request, TypeEngin $typeEngin)
    {
        $validated = $request->validate([
            'type' => 'sometimes|required|string|max:255',
        ]);

        $typeEngin->update($validated);
        return response()->json($typeEngin);
    }

    public function destroy(TypeEngin $typeEngin)
    {
        $typeEngin->delete();
        return response()->json(null, 204);
    }

    public function getEngins(TypeEngin $typeEngin)
    {
        return response()->json($typeEngin->engins);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Engin;
use Illuminate\Http\Request;

class EnginController extends Controller
{
    public function index()
    {
        $engins = Engin::with(['utilisateur', 'typeEngin'])->get();
        return response()->json($engins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'immatriculation' => 'required|string|max:255',
            'type_engin' => 'required|exists:type_engins,id',
            'photo' => 'nullable|string|max:255',
        ]);

        $engin = Engin::create($validated);
        return response()->json($engin->load(['utilisateur', 'typeEngin']), 201);
    }

    public function show(Engin $engin)
    {
        return response()->json($engin->load(['utilisateur', 'typeEngin', 'contrats']));
    }

    public function update(Request $request, Engin $engin)
    {
        $validated = $request->validate([
            'utilisateur_id' => 'sometimes|required|exists:utilisateurs,id',
            'marque' => 'sometimes|required|string|max:255',
            'modele' => 'sometimes|required|string|max:255',
            'immatriculation' => 'sometimes|required|string|max:255',
            'type_engin' => 'sometimes|required|exists:type_engins,id',
            'photo' => 'nullable|string|max:255',
        ]);

        $engin->update($validated);
        return response()->json($engin->load(['utilisateur', 'typeEngin']));
    }

    public function destroy(Engin $engin)
    {
        $engin->delete();
        return response()->json(null, 204);
    }

    public function getByUtilisateur($utilisateurId)
    {
        $engins = Engin::where('utilisateur_id', $utilisateurId)
            ->with(['utilisateur', 'typeEngin'])
            ->get();
        return response()->json($engins);
    }

    public function getByType($typeId)
    {
        $engins = Engin::where('type_engin', $typeId)
            ->with(['utilisateur', 'typeEngin'])
            ->get();
        return response()->json($engins);
    }

    public function getPositions(Engin $engin)
    {
        return response()->json($engin->positions);
    }

    public function getContrats(Engin $engin)
    {
        return response()->json($engin->contrats);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with(['engin', 'utilisateur'])->get();
        return response()->json($positions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'engin_id' => 'required|exists:engins,engin_id',
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'date_position' => 'nullable|date',
        ]);

        $position = Position::create($validated);
        return response()->json($position->load(['engin', 'utilisateur']), 201);
    }

    public function show(Position $position)
    {
        return response()->json($position->load(['engin', 'utilisateur']));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'engin_id' => 'sometimes|required|exists:engins,engin_id',
            'utilisateur_id' => 'sometimes|required|exists:utilisateurs,id',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'date_position' => 'nullable|date',
        ]);

        $position->update($validated);
        return response()->json($position->load(['engin', 'utilisateur']));
    }

    public function destroy(Position $position)
    {
        $position->delete();
        return response()->json(null, 204);
    }

    public function getByEngin($enginId)
    {
        $positions = Position::where('engin_id', $enginId)
            ->with(['engin', 'utilisateur'])
            ->orderBy('date_position', 'desc')
            ->get();
        return response()->json($positions);
    }

    public function getByUtilisateur($utilisateurId)
    {
        $positions = Position::where('utilisateur_id', $utilisateurId)
            ->with(['engin', 'utilisateur'])
            ->orderBy('date_position', 'desc')
            ->get();
        return response()->json($positions);
    }

    public function getLastPosition($enginId)
    {
        $position = Position::where('engin_id', $enginId)
            ->with(['engin', 'utilisateur'])
            ->orderBy('date_position', 'desc')
            ->first();
        return response()->json($position);
    }
}

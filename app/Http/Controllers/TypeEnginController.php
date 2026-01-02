<?php

namespace App\Http\Controllers;

use App\Models\TypeEngin;
use Illuminate\Http\Request;

class TypeEnginController extends Controller
{
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

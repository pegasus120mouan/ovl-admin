<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Engin;
use App\Models\TypeEngin;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EnginController extends Controller
{
    public function indexWeb(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $engins = Engin::with(['utilisateur'])
            ->orderByDesc('engin_id')
            ->paginate($perPage)
            ->withQueryString();

        $totalEngins = Engin::count();

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $colisRecusMois = Commande::query()
            ->whereBetween('date_reception', [$startOfMonth, $endOfMonth])
            ->count();

        $colisLivresMois = Commande::query()
            ->livre()
            ->whereBetween('date_reception', [$startOfMonth, $endOfMonth])
            ->count();

        $colisNonLivresMois = Commande::query()
            ->nonLivre()
            ->whereBetween('date_reception', [$startOfMonth, $endOfMonth])
            ->count();

        $colisRetoursMois = Commande::query()
            ->retour()
            ->whereBetween('date_reception', [$startOfMonth, $endOfMonth])
            ->count();

        $typesEngins = TypeEngin::query()
            ->orderBy('type')
            ->get();

        $livreurs = Utilisateur::query()
            ->livreurs()
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        return view('engins.index', compact(
            'engins',
            'totalEngins',
            'colisRecusMois',
            'colisLivresMois',
            'colisNonLivresMois',
            'colisRetoursMois',
            'typesEngins',
            'livreurs'
        ));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'annee_fabrication' => 'nullable|integer|min:1900|max:2100',
            'plaque_immatriculation' => 'nullable|string|max:20',
            'numero_chassis' => 'nullable|string|max:255',
            'couleur' => 'nullable|string|max:50',
            'marque' => 'nullable|string|max:255',
            'type_engin' => 'required|string|max:50',
            'utilisateur_id' => 'nullable|integer|exists:utilisateurs,id',
        ]);

        $validated['date_ajout'] = Carbon::now()->toDateString();

        Engin::create($validated);

        return redirect()->route('engins.index')->with('success', "Engin enregistré avec succès");
    }

    public function updateWeb(Request $request, Engin $engin)
    {
        $validated = $request->validate([
            'annee_fabrication' => 'nullable|integer|min:1900|max:2100',
            'plaque_immatriculation' => 'nullable|string|max:20',
            'numero_chassis' => 'nullable|string|max:255',
            'couleur' => 'nullable|string|max:50',
            'marque' => 'nullable|string|max:255',
            'type_engin' => 'required|string|max:50',
            'utilisateur_id' => 'nullable|integer|exists:utilisateurs,id',
            'statut' => 'nullable|string|max:50',
        ]);

        $engin->update($validated);

        return redirect()->route('engins.index')->with('success', "Engin modifié avec succès");
    }

    public function destroyWeb(Engin $engin)
    {
        $engin->delete();

        return redirect()->route('engins.index')->with('success', "Engin supprimé avec succès");
    }

    public function showWeb(Engin $engin)
    {
        $engin->load(['utilisateur']);

        return view('engins.info_engins', compact('engin'));
    }

    public function updateImagesWeb(Request $request, Engin $engin)
    {
        $validated = $request->validate([
            'image_1' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_2' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_3' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_4' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $targetDir = public_path('img/engins');

        $updates = [];
        foreach (['image_1', 'image_2', 'image_3', 'image_4'] as $field) {
            /** @var UploadedFile|null $file */
            $file = $request->file($field);
            if (!$file) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'engin_' . $engin->engin_id . '_' . $field . '_' . time() . '.' . $extension;

            $stored = false;
            $storedKey = null;
            try {
                $disk = Storage::disk('s3');
                $disk->putFileAs('engins', $file, $filename);
                $stored = true;
                $storedKey = 'engins/' . $filename;
            } catch (\Throwable $e) {
                $stored = false;
            }

            if (!$stored) {
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
            }

            $updates[$field] = $stored && $storedKey ? $storedKey : $filename;
        }

        if (!empty($updates)) {
            $engin->update($updates);
        }

        return redirect()->route('engins.show', $engin)->with('success', "Images modifiées avec succès");
    }

    public function index()
    {
        $engins = Engin::with(['utilisateur'])->get();
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

<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Contrat;
use App\Models\Engin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function indexWeb(Request $request)
    {
        $perPage = $request->integer('per_page', 20);

        $contrats = Contrat::query()
            ->with(['engin.utilisateur'])
            ->orderByDesc('contrat_id')
            ->paginate($perPage)
            ->withQueryString();

        $today = Carbon::today();

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

        $engins = Engin::query()
            ->orderBy('plaque_immatriculation')
            ->orderBy('numero_chassis')
            ->get(['engin_id', 'plaque_immatriculation', 'numero_chassis']);

        $contrats->getCollection()->transform(function (Contrat $contrat) use ($today) {
            $vignetteFin = $contrat->vignette_date_fin;
            $assuranceFin = $contrat->assurance_date_fin;

            $contrat->vignette_jours_restants = $vignetteFin ? $today->diffInDays($vignetteFin, false) : null;
            $contrat->assurance_jours_restants = $assuranceFin ? $today->diffInDays($assuranceFin, false) : null;

            return $contrat;
        });

        return view('engins.contrats_engins', compact(
            'contrats',
            'colisRecusMois',
            'colisLivresMois',
            'colisNonLivresMois',
            'colisRetoursMois',
            'engins'
        ));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'id_engin' => 'required|integer|exists:engins,engin_id',
            'vignette_date_debut' => 'required|date',
            'vignette_date_fin' => 'required|date|after_or_equal:vignette_date_debut',
            'assurance_date_debut' => 'required|date',
            'assurance_date_fin' => 'required|date|after_or_equal:assurance_date_debut',
        ]);

        Contrat::create($validated);

        return redirect()->route('engins.contrats_engins')->with('success', "Contrat enregistré avec succès");
    }

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

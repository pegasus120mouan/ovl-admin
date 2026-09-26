<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use App\Models\Utilisateur;
use App\Models\Versement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class DettesInternesController extends Controller
{
    public function index()
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $dettes = Dette::query()
            ->with(['livreur', 'versements' => function ($q) {
                $q->orderByDesc('date_versement')->orderByDesc('id');
            }])
            ->orderByDesc('date_dette')
            ->orderByDesc('id')
            ->get();

        $dettes->each(function ($d) {
            $montantActuel = (int) ($d->montant_actuel ?? 0);
            $montantsPayes = (int) ($d->montants_payes ?? 0);
            if ($montantsPayes < 0) {
                $montantsPayes = 0;
            }
            if ($montantsPayes > $montantActuel) {
                $montantsPayes = $montantActuel;
            }

            $d->montant_actuel = $montantActuel;
            $d->montants_payes = $montantsPayes;
            $d->reste = max(0, $montantActuel - $montantsPayes);
            $d->statut = ($d->reste ?? 0) > 0 ? 'En cours' : 'Soldée';
        });

        $totalReste = (int) $dettes->sum(fn ($d) => (int) ($d->reste ?? 0));

        $livreurs = Utilisateur::query()
            ->livreurs()
            ->orderByDesc('statut_compte')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        return view('dettes_internes.index', compact('dettes', 'totalReste', 'livreurs'));
    }

    private function validateDebiteur(Request $request): array
    {
        $validated = $request->validate([
            'debiteur_type' => 'required|in:livreur,autre',
            'livreur_id' => 'required_if:debiteur_type,livreur|nullable|integer|exists:utilisateurs,id',
            'type_dette' => 'required_if:debiteur_type,livreur|nullable|in:' . implode(',', Dette::TYPES_LIVREUR),
            'nom_debiteur' => 'required_if:debiteur_type,autre|nullable|string|max:255',
        ]);

        if ($validated['debiteur_type'] === 'livreur') {
            $livreur = Utilisateur::query()->livreurs()->findOrFail($validated['livreur_id']);

            return [
                'livreur_id' => $livreur->id,
                'nom_debiteur' => trim(($livreur->nom ?? '') . ' ' . ($livreur->prenoms ?? '')),
                'type' => $validated['type_dette'],
            ];
        }

        return [
            'livreur_id' => null,
            'nom_debiteur' => $validated['nom_debiteur'],
            'type' => 'A payer',
        ];
    }

    public function store(Request $request)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'remboursable' => 'required|in:0,1',
            'motifs' => 'nullable|string',
            'montant_initial' => 'required|integer|min:0',
            'date_dette' => 'required|date',
            'date_echeance' => 'nullable|date',
        ]);
        $debiteur = $this->validateDebiteur($request);

        $montantInitial = (int) $validated['montant_initial'];
        $remboursable = (bool) ((int) $validated['remboursable']);
        $dateDette = $validated['date_dette'];
        $dateEcheance = $remboursable ? ($validated['date_echeance'] ?? null) : $dateDette;

        $payload = [
            'remboursable' => $remboursable,
            'livreur_id' => $debiteur['livreur_id'],
            'nom_debiteur' => $debiteur['nom_debiteur'],
            'type' => $debiteur['type'],
            'montant_initial' => $montantInitial,
            'montant_actuel' => $montantInitial,
            'montants_payes' => 0,
            'reste' => $montantInitial,
            'date_dette' => $dateDette,
            'date_echeance' => $dateEcheance,
            'statut' => $montantInitial > 0 ? 'En cours' : 'Soldée',
        ];

        if (Schema::hasColumn('dette', 'motifs')) {
            $payload['motifs'] = $validated['motifs'] ?? '';
        }

        Dette::create($payload);

        return redirect()->back()->with('success', 'Dette interne ajoutée.');
    }

    public function update(Request $request, Dette $dette)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'remboursable' => 'required|in:0,1',
            'motifs' => 'nullable|string',
            'montant_actuel' => 'required|integer|min:0',
            'date_dette' => 'required|date',
            'date_echeance' => 'nullable|date',
        ]);
        $debiteur = $this->validateDebiteur($request);

        $montantActuel = (int) $validated['montant_actuel'];
        $remboursable = (bool) ((int) $validated['remboursable']);
        $dateDette = $validated['date_dette'];
        $dateEcheance = $remboursable ? ($validated['date_echeance'] ?? null) : $dateDette;

        DB::transaction(function () use ($dette, $validated, $debiteur, $montantActuel, $remboursable, $dateDette, $dateEcheance) {
            $dette->remboursable = $remboursable;
            $dette->livreur_id = $debiteur['livreur_id'];
            $dette->nom_debiteur = $debiteur['nom_debiteur'];
            $dette->type = $debiteur['type'];
            $dette->montant_actuel = $montantActuel;
            $dette->date_dette = $dateDette;
            $dette->date_echeance = $dateEcheance;

            if (Schema::hasColumn('dette', 'motifs')) {
                $dette->motifs = $validated['motifs'] ?? ($dette->motifs ?? '');
            }

            $montantsPayes = (int) ($dette->montants_payes ?? 0);
            if ($montantsPayes > $montantActuel) {
                $montantsPayes = $montantActuel;
            }

            $dette->montants_payes = $montantsPayes;
            $dette->reste = max(0, $montantActuel - $montantsPayes);
            $dette->statut = $dette->reste > 0 ? 'En cours' : 'Soldée';
            $dette->save();
        });

        return redirect()->back()->with('success', 'Dette interne modifiée.');
    }

    public function destroy(Dette $dette)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        DB::transaction(function () use ($dette) {
            $dette->versements()->delete();
            $dette->delete();
        });
        return redirect()->back()->with('success', 'Dette supprimée.');
    }

    public function storeVersement(Request $request, Dette $dette)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        if (!(bool) ($dette->remboursable ?? true)) {
            return redirect()->back()->with('error', 'Cette dette n\'est pas remboursable.');
        }

        $validated = $request->validate([
            'montant_versement' => 'required|integer|min:1',
            'date_versement' => 'nullable|date',
        ]);

        $dateVersement = $validated['date_versement'] ?? Carbon::today()->toDateString();
        $montant = (int) $validated['montant_versement'];

        $montantPaye = 0;
        $resteApres = (int) ($dette->reste ?? 0);

        DB::transaction(function () use ($dette, $montant, $dateVersement, &$montantPaye, &$resteApres) {
            $reste = (int) ($dette->reste ?? 0);
            if ($reste <= 0) {
                return;
            }

            $montantEffectif = min($montant, $reste);
            $montantPaye = $montantEffectif;

            Versement::create([
                'dette_id' => $dette->id,
                'montant_versement' => $montantEffectif,
                'date_versement' => $dateVersement,
            ]);

            $totalVerse = (int) Versement::query()->where('dette_id', $dette->id)->sum('montant_versement');

            $dette->montants_payes = $totalVerse;
            $dette->reste = max(0, (int) ($dette->montant_actuel ?? 0) - $totalVerse);
            $dette->statut = $dette->reste > 0 ? 'En cours' : 'Soldée';
            $dette->save();

            $resteApres = (int) $dette->reste;
        });

        if ($montantPaye <= 0) {
            return redirect()->back()->with('error', 'Aucun versement enregistré : cette dette est déjà soldée.');
        }

        $dateAffichee = Carbon::parse($dateVersement)->format('d/m/Y');
        $montantDette = number_format((int) ($dette->fresh()->montant_actuel ?? 0), 0, ',', ' ');
        $montantPayeFmt = number_format($montantPaye, 0, ',', ' ');
        $resteFmt = number_format($resteApres, 0, ',', ' ');

        return redirect()->back()->with(
            'success',
            "Versement du {$dateAffichee} : {$montantPayeFmt} XOF payés. Montant dette : {$montantDette} XOF. Reste : {$resteFmt} XOF."
        );
    }
}

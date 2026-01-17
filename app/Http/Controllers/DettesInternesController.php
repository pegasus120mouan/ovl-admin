<?php

namespace App\Http\Controllers;

use App\Models\Dette;
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
            ->with(['versements' => function ($q) {
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

        return view('dettes_internes.index', compact('dettes', 'totalReste'));
    }

    public function store(Request $request)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'remboursable' => 'required|in:0,1',
            'nom_debiteur' => 'required|string|max:255',
            'motifs' => 'nullable|string',
            'montant_initial' => 'required|integer|min:0',
            'date_dette' => 'required|date',
            'date_echeance' => 'nullable|date',
        ]);

        $montantInitial = (int) $validated['montant_initial'];
        $remboursable = (bool) ((int) $validated['remboursable']);
        $dateDette = $validated['date_dette'];
        $dateEcheance = $remboursable ? ($validated['date_echeance'] ?? null) : $dateDette;

        $payload = [
            'remboursable' => $remboursable,
            'nom_debiteur' => $validated['nom_debiteur'],
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
            'nom_debiteur' => 'required|string|max:255',
            'motifs' => 'nullable|string',
            'montant_actuel' => 'required|integer|min:0',
            'date_dette' => 'required|date',
            'date_echeance' => 'nullable|date',
        ]);

        $montantActuel = (int) $validated['montant_actuel'];
        $remboursable = (bool) ((int) $validated['remboursable']);
        $dateDette = $validated['date_dette'];
        $dateEcheance = $remboursable ? ($validated['date_echeance'] ?? null) : $dateDette;

        DB::transaction(function () use ($dette, $validated, $montantActuel, $remboursable, $dateDette, $dateEcheance) {
            $dette->remboursable = $remboursable;
            $dette->nom_debiteur = $validated['nom_debiteur'];
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

        DB::transaction(function () use ($dette, $montant, $dateVersement) {
            $reste = (int) ($dette->reste ?? 0);
            if ($reste <= 0) {
                return;
            }

            $montantEffectif = min($montant, $reste);

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
        });

        return redirect()->back()->with('success', 'Versement enregistré.');
    }
}

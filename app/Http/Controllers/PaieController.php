<?php

namespace App\Http\Controllers;

use App\Models\PaieAjustement;
use App\Models\PaieLivreur;
use App\Models\PaiePeriode;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaieController extends Controller
{
    private function currentUserId(): ?int
    {
        $u = Session::get('utilisateur');
        if (is_array($u) && isset($u['id'])) {
            return (int) $u['id'];
        }

        return null;
    }

    private function recalcFiche(PaieLivreur $fiche): void
    {
        $totalAjustements = (int) $fiche->ajustements()->where('statut', 'Approuvé')->sum('montant');

        $fiche->total_ajustements = $totalAjustements;
        $fiche->net_a_payer = ((int) $fiche->salaire_base) + $totalAjustements;
        $fiche->save();
    }

    private function syncPeriodeStatut(PaiePeriode $periode): void
    {
        $periodeId = (int) $periode->id;
        if ($periodeId <= 0) {
            return;
        }

        $totalFiches = PaieLivreur::query()->where('periode_id', $periodeId)->count();
        if ($totalFiches <= 0) {
            return;
        }

        $totalPayees = PaieLivreur::query()->where('periode_id', $periodeId)->where('statut', 'Payé')->count();
        if ($totalPayees === $totalFiches && ($periode->statut ?? null) !== 'Payé') {
            $periode->statut = 'Payé';
            $periode->save();
        }
    }

    public function index(Request $request)
    {
        $totalMontantPaye = (int) PaieLivreur::query()
            ->where('statut', 'Payé')
            ->sum(DB::raw('COALESCE(montant_paye, net_a_payer)'));

        $statsPeriodesRows = PaiePeriode::query()
            ->select('statut', DB::raw('COUNT(*) as total'))
            ->groupBy('statut')
            ->get();

        $statsPeriodes = [
            'total' => (int) PaiePeriode::query()->count(),
            'brouillon' => 0,
            'en_cours' => 0,
            'paye' => 0,
            'montant_total_paye' => $totalMontantPaye,
        ];

        foreach ($statsPeriodesRows as $row) {
            $statut = (string) ($row->statut ?? '');
            $count = (int) ($row->total ?? 0);
            if ($statut === 'Brouillon') {
                $statsPeriodes['brouillon'] = $count;
            } elseif ($statut === 'En cours') {
                $statsPeriodes['en_cours'] = $count;
            } elseif ($statut === 'Payé') {
                $statsPeriodes['paye'] = $count;
            }
        }

        $periodes = PaiePeriode::query()
            ->orderByDesc('date_debut')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $periodeIds = $periodes->getCollection()->pluck('id')->filter()->values();
        if ($periodeIds->isNotEmpty()) {
            $stats = PaieLivreur::query()
                ->select(
                    'periode_id',
                    DB::raw('COUNT(*) as total_fiches'),
                    DB::raw("SUM(CASE WHEN statut = 'Payé' THEN 1 ELSE 0 END) as total_payees")
                )
                ->whereIn('periode_id', $periodeIds)
                ->groupBy('periode_id')
                ->get()
                ->keyBy('periode_id');

            $toPay = [];
            foreach ($periodes as $periode) {
                $s = $stats->get($periode->id);
                if ($s && ((int) $s->total_fiches) > 0 && ((int) $s->total_fiches) === ((int) $s->total_payees)) {
                    if (($periode->statut ?? null) !== 'Payé') {
                        $toPay[] = (int) $periode->id;
                        $periode->statut = 'Payé';
                    }
                }
            }

            if (!empty($toPay)) {
                PaiePeriode::query()->whereIn('id', $toPay)->update(['statut' => 'Payé']);
            }
        }

        return view('paies.periodes_index', compact('periodes', 'statsPeriodes'));
    }

    public function storePeriode(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'libelle' => 'nullable|string|max:255',
        ]);

        if (Carbon::parse($validated['date_fin'])->lt(Carbon::parse($validated['date_debut']))) {
            return redirect()->back()->with('error', 'La date de fin doit être supérieure ou égale à la date de début.');
        }

        $dateDebut = Carbon::parse($validated['date_debut'])->toDateString();
        $dateFin = Carbon::parse($validated['date_fin'])->toDateString();

        $exists = PaiePeriode::query()
            ->whereDate('date_debut', $dateDebut)
            ->whereDate('date_fin', $dateFin)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Cette période existe déjà.');
        }

        $libelle = (string) ($validated['libelle'] ?? '');
        if ($libelle === '') {
            $libelle = 'Paie du ' . Carbon::parse($dateDebut)->format('d/m/Y') . ' au ' . Carbon::parse($dateFin)->format('d/m/Y');
        }

        $periode = PaiePeriode::create([
            'libelle' => $libelle,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'statut' => 'Brouillon',
        ]);

        return redirect()->route('paies.periodes.show', $periode)->with('success', 'Période créée.');
    }

    public function showPeriode(PaiePeriode $periode)
    {
        $this->syncPeriodeStatut($periode);
        $periode->load(['fiches.livreur']);

        $fiches = PaieLivreur::query()
            ->with('livreur')
            ->where('periode_id', $periode->id)
            ->orderBy('statut')
            ->orderBy('livreur_id')
            ->paginate(30)
            ->withQueryString();

        $livreurs = Utilisateur::query()->livreurs()->orderBy('nom')->orderBy('prenoms')->get();

        return view('paies.periode_show', compact('periode', 'fiches', 'livreurs'));
    }

    public function genererFiches(Request $request, PaiePeriode $periode)
    {
        if (($periode->statut ?? null) !== 'Brouillon') {
            return redirect()->back()->with('error', 'Impossible de générer des fiches sur une période non brouillon.');
        }

        $validated = $request->validate([
            'livreur_id' => 'nullable|integer|exists:utilisateurs,id',
            'uniquement_actifs' => 'nullable|boolean',
        ]);

        $onlyActifs = (bool) ($validated['uniquement_actifs'] ?? true);

        $livreursQuery = Utilisateur::query()->livreurs();
        if ($onlyActifs) {
            $livreursQuery->where('statut_compte', 1);
        }

        if (!empty($validated['livreur_id'])) {
            $livreursQuery->where('id', (int) $validated['livreur_id']);
        }

        $livreurs = $livreursQuery->get();

        DB::transaction(function () use ($periode, $livreurs) {
            foreach ($livreurs as $livreur) {
                $mensuel = (int) ($livreur->salaire_mensuel ?? 0);
                $base = (int) round($mensuel / 2);

                $fiche = PaieLivreur::query()->firstOrCreate([
                    'periode_id' => $periode->id,
                    'livreur_id' => $livreur->id,
                ], [
                    'salaire_base' => $base,
                    'total_ajustements' => 0,
                    'net_a_payer' => $base,
                    'statut' => 'Brouillon',
                ]);

                if (($fiche->statut ?? null) === 'Brouillon') {
                    $fiche->salaire_base = $base;
                    $fiche->save();

                    $this->recalcFiche($fiche);
                }
            }
        });

        return redirect()->back()->with('success', 'Fiches générées / mises à jour.');
    }

    public function showFiche(PaieLivreur $fiche)
    {
        $fiche->load(['periode', 'livreur', 'ajustements']);

        return view('paies.fiche_show', compact('fiche'));
    }

    public function storeAjustement(Request $request, PaieLivreur $fiche)
    {
        if (($fiche->statut ?? null) !== 'Brouillon') {
            return redirect()->back()->with('error', 'Impossible d\'ajouter un ajustement sur une fiche non brouillon.');
        }

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'montant' => 'required|integer',
            'motif' => 'required|string|max:255',
            'commande_id' => 'nullable|integer',
        ]);

        $userId = $this->currentUserId();

        PaieAjustement::create([
            'paie_livreur_id' => $fiche->id,
            'livreur_id' => $fiche->livreur_id,
            'periode_id' => $fiche->periode_id,
            'type' => $validated['type'],
            'montant' => (int) $validated['montant'],
            'motif' => $validated['motif'],
            'statut' => 'En attente',
            'cree_par' => $userId,
            'commande_id' => $validated['commande_id'] ?? null,
        ]);

        $this->recalcFiche($fiche);

        return redirect()->back()->with('success', 'Ajustement ajouté.');
    }

    public function approuverAjustement(PaieAjustement $ajustement)
    {
        $fiche = PaieLivreur::query()->findOrFail($ajustement->paie_livreur_id);

        if (($fiche->statut ?? null) !== 'Brouillon') {
            return redirect()->back()->with('error', 'Impossible de valider un ajustement sur une fiche non brouillon.');
        }

        $ajustement->statut = 'Approuvé';
        $ajustement->valide_par = $this->currentUserId();
        $ajustement->date_validation = now()->toDateString();
        $ajustement->save();

        $this->recalcFiche($fiche);

        return redirect()->back()->with('success', 'Ajustement approuvé.');
    }

    public function refuserAjustement(PaieAjustement $ajustement)
    {
        $fiche = PaieLivreur::query()->findOrFail($ajustement->paie_livreur_id);

        if (($fiche->statut ?? null) !== 'Brouillon') {
            return redirect()->back()->with('error', 'Impossible de refuser un ajustement sur une fiche non brouillon.');
        }

        $ajustement->statut = 'Refusé';
        $ajustement->valide_par = $this->currentUserId();
        $ajustement->date_validation = now()->toDateString();
        $ajustement->save();

        $this->recalcFiche($fiche);

        return redirect()->back()->with('success', 'Ajustement refusé.');
    }

    public function validerFiche(PaieLivreur $fiche)
    {
        if (($fiche->statut ?? null) !== 'Brouillon') {
            return redirect()->back()->with('error', 'Cette fiche n\'est pas en brouillon.');
        }

        $this->recalcFiche($fiche);

        $fiche->statut = 'Validé';
        $fiche->date_validation = now()->toDateString();
        $fiche->valide_par = $this->currentUserId();
        $fiche->save();

        $periode = PaiePeriode::query()->find($fiche->periode_id);
        if ($periode && ($periode->statut ?? null) === 'Brouillon') {
            $periode->statut = 'En cours';
            $periode->save();
        }

        return redirect()->back()->with('success', 'Fiche validée.');
    }

    public function payerFiche(Request $request, PaieLivreur $fiche)
    {
        if (!in_array(($fiche->statut ?? null), ['Validé', 'Payé'], true)) {
            return redirect()->back()->with('error', 'Cette fiche doit être validée avant paiement.');
        }

        $validated = $request->validate([
            'date_paiement' => 'required|date',
            'montant_paye' => 'required|integer|min:0',
            'reference_paiement' => 'nullable|string|max:255',
        ]);

        $fiche->date_paiement = Carbon::parse($validated['date_paiement'])->toDateString();
        $fiche->montant_paye = (int) $validated['montant_paye'];
        $fiche->reference_paiement = $validated['reference_paiement'] ?? null;
        $fiche->paye_par = $this->currentUserId();
        $fiche->statut = 'Payé';
        $fiche->save();

        $periodeId = (int) $fiche->periode_id;
        if ($periodeId > 0) {
            $totalFiches = PaieLivreur::query()->where('periode_id', $periodeId)->count();
            $totalPayees = PaieLivreur::query()->where('periode_id', $periodeId)->where('statut', 'Payé')->count();

            if ($totalFiches > 0 && $totalFiches === $totalPayees) {
                PaiePeriode::query()->where('id', $periodeId)->update(['statut' => 'Payé']);
            }
        }

        return redirect()->route('paies.periodes.show', $fiche->periode_id)->with('success', 'Fiche marquée comme payée.');
    }

    public function destroyPeriode(PaiePeriode $periode)
    {
        DB::transaction(function () use ($periode) {
            $ficheIds = PaieLivreur::query()->where('periode_id', $periode->id)->pluck('id');

            if ($ficheIds->isNotEmpty()) {
                PaieAjustement::query()->whereIn('paie_livreur_id', $ficheIds)->delete();
                PaieLivreur::query()->whereIn('id', $ficheIds)->delete();
            }

            $periode->delete();
        });

        return redirect()->route('paies.periodes.index')->with('success', 'Période supprimée.');
    }
}

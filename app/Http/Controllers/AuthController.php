<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Dette;
use App\Models\Facture;
use App\Models\PaieLivreur;
use App\Models\PaiePeriode;
use App\Models\PointsLivreur;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Session::has('utilisateur')) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = hash('sha256', $request->input('password'));

        $utilisateur = Utilisateur::where('login', $login)
            ->where('password', $password)
            ->first();

        if ($utilisateur) {
            if ($utilisateur->statut_compte == 0) {
                return back()->withErrors(['login' => 'Votre compte est désactivé. Contactez l\'administrateur.'])->withInput();
            }

            Session::put('utilisateur', [
                'id' => $utilisateur->id,
                'nom' => $utilisateur->nom,
                'prenoms' => $utilisateur->prenoms,
                'login' => $utilisateur->login,
                'role' => $utilisateur->role,
                'avatar' => $utilisateur->avatar,
                'boutique_id' => $utilisateur->boutique_id,
            ]);

            if ($request->has('remember')) {
                Session::put('remember', true);
            }

            return redirect()->route('dashboard')->with('success', 'Connexion réussie!');
        }

        return back()->withErrors(['login' => 'Login ou mot de passe incorrect.'])->withInput();
    }

    public function logout()
    {
        Session::forget('utilisateur');
        Session::flush();
        return redirect()->route('login')->with('success', 'Déconnexion réussie!');
    }

    public function dashboard()
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $totalPaieMontantPaye = (int) PaieLivreur::query()
            ->where('statut', 'Payé')
            ->sum(DB::raw('COALESCE(montant_paye, net_a_payer)'));
        $nbFichesPayees = (int) PaieLivreur::query()->where('statut', 'Payé')->count();
        $nbFichesAPayer = (int) PaieLivreur::query()->where('statut', '!=', 'Payé')->count();
        $nbPeriodesPayees = (int) PaiePeriode::query()->where('statut', 'Payé')->count();
        $nbPeriodesEnCours = (int) PaiePeriode::query()->where('statut', 'En cours')->count();

        $startOfYear = Carbon::now()->startOfYear()->toDateString();
        $today = Carbon::now()->toDateString();

        $facturesParStatut = Facture::query()
            ->whereDate('date_facture', '>=', $startOfYear)
            ->whereDate('date_facture', '<=', $today)
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $montantFacturesTotal = (int) Facture::query()->sum('total_ttc');

        $montantDettesTotal = (int) Dette::query()->sum('montant_actuel');

        $montantDettesRembourseesTotal = (int) Dette::query()->sum('montants_payes');

        $montantDettesRestantTotal = max(0, (int) $montantDettesTotal - (int) $montantDettesRembourseesTotal);

        $montantFacturesValideesAnnee = (int) Facture::query()
            ->where('statut', 'Validé')
            ->whereDate('date_facture', '>=', $startOfYear)
            ->whereDate('date_facture', '<=', $today)
            ->sum('total_ttc');

        $nbFacturesBrouillon = (int) ($facturesParStatut['Brouillon'] ?? 0);
        $nbFacturesValidees = (int) ($facturesParStatut['Validé'] ?? 0);
        $nbFacturesPayees = (int) ($facturesParStatut['Payé'] ?? 0);
        $nbFacturesTotal = $nbFacturesBrouillon + $nbFacturesValidees + $nbFacturesPayees;

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $nbColisLivresMois = (int) Commande::query()
            ->where('statut', 'Livré')
            ->whereBetween('date_livraison', [$startOfMonth, $today])
            ->count();

        $montantLivraisonsPayeesMois = (int) Commande::query()
            ->where('statut', 'Livré')
            ->whereBetween('date_livraison', [$startOfMonth, $today])
            ->sum('cout_livraison');

        $montantFacturesPayeesMois = (int) Facture::query()
            ->where('statut', 'Payé')
            ->whereDate('date_facture', '>=', $startOfMonth)
            ->whereDate('date_facture', '<=', $today)
            ->sum('total_ttc');

        $depensesLivreursMois = (int) PointsLivreur::query()
            ->whereBetween('date_commande', [$startOfMonth, $today])
            ->sum('depense');

        $paieLivreursMois = (int) PaieLivreur::query()
            ->where('statut', 'Payé')
            ->whereDate('date_paiement', '>=', $startOfMonth)
            ->whereDate('date_paiement', '<=', $today)
            ->sum(DB::raw('COALESCE(montant_paye, net_a_payer)'));

        $depensesMois = (int) $depensesLivreursMois
            + (int) $paieLivreursMois
            + (int) $montantDettesRestantTotal;

        $gainMois = ((int) $montantLivraisonsPayeesMois + (int) $montantFacturesPayeesMois) - (int) $depensesMois;

        $paiementLivreursAnnee = (int) PaieLivreur::query()
            ->where('statut', 'Payé')
            ->whereDate('date_paiement', '>=', $startOfYear)
            ->whereDate('date_paiement', '<=', $today)
            ->sum(DB::raw('COALESCE(montant_paye, net_a_payer)'));

        $nbColisRecusAnnee = Commande::whereBetween('date_reception', [$startOfYear, $today])->count();
        $nbColisLivresAnnee = Commande::where('statut', 'Livré')->whereBetween('date_livraison', [$startOfYear, $today])->count();
        $nbColisNonLivresAnnee = Commande::where('statut', 'Non Livré')->whereBetween('date_reception', [$startOfYear, $today])->count();
        $nbColisRetoursAnnee = Commande::where('statut', 'Retour')->whereBetween('date_retour', [$startOfYear, $today])->count();

        $revenusTotalAnnee = (int) Commande::where('statut', 'Livré')->whereBetween('date_livraison', [$startOfYear, $today])->sum('cout_global');
        $montantLivraisonsValideesAnnee = (int) Commande::where('statut', 'Livré')->whereBetween('date_livraison', [$startOfYear, $today])->sum('cout_global');
        $chargesVariablesAnnee = (int) Commande::where('statut', 'Livré')->whereBetween('date_livraison', [$startOfYear, $today])->sum('cout_livraison');
        $chargesFixesAnnee = (int) PointsLivreur::query()
            ->whereBetween('date_commande', [$startOfYear, $today])
            ->sum('depense');
        $epargnesAnnee = (int) PointsLivreur::query()
            ->whereBetween('date_commande', [$startOfYear, $today])
            ->sum('gain_jour');

        $gainSyntheseAnnee = (int) $chargesVariablesAnnee - ((int) $chargesFixesAnnee + (int) $paiementLivreursAnnee);

        $totalCommandesAnnee = (int) Commande::query()
            ->whereDate('date_reception', '>=', $startOfYear)
            ->whereDate('date_reception', '<=', $today)
            ->count();

        $topBoutiques = Commande::query()
            ->join('utilisateurs as u', 'commandes.utilisateur_id', '=', 'u.id')
            ->leftJoin('boutiques as b', 'u.boutique_id', '=', 'b.id')
            ->whereDate('commandes.date_reception', '>=', $startOfYear)
            ->whereDate('commandes.date_reception', '<=', $today)
            ->selectRaw("COALESCE(b.nom, 'Sans boutique') as label, COUNT(*) as total")
            ->groupBy('b.id', 'b.nom')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $repartitionBoutiquesLabels = $topBoutiques->pluck('label')->values()->all();
        $repartitionBoutiquesData = $topBoutiques->pluck('total')->map(fn ($v) => (int) $v)->values()->all();
        $autreBoutiques = $totalCommandesAnnee - array_sum($repartitionBoutiquesData);
        if ($autreBoutiques > 0) {
            $repartitionBoutiquesLabels[] = 'Autre';
            $repartitionBoutiquesData[] = (int) $autreBoutiques;
        }

        $topClients = Commande::query()
            ->join('utilisateurs as u', 'commandes.utilisateur_id', '=', 'u.id')
            ->whereDate('commandes.date_reception', '>=', $startOfYear)
            ->whereDate('commandes.date_reception', '<=', $today)
            ->selectRaw("CONCAT(COALESCE(u.nom,''), ' ', COALESCE(u.prenoms,'')) as label, COUNT(*) as total")
            ->groupBy('u.id', 'u.nom', 'u.prenoms')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $repartitionClientsLabels = $topClients->pluck('label')->map(fn ($v) => trim((string) $v) ?: 'N/A')->values()->all();
        $repartitionClientsData = $topClients->pluck('total')->map(fn ($v) => (int) $v)->values()->all();
        $autreClients = $totalCommandesAnnee - array_sum($repartitionClientsData);
        if ($autreClients > 0) {
            $repartitionClientsLabels[] = 'Autre';
            $repartitionClientsData[] = (int) $autreClients;
        }

        $topGainsLivreurs = PointsLivreur::query()
            ->join('utilisateurs as u', 'points_livreurs.utilisateur_id', '=', 'u.id')
            ->whereDate('points_livreurs.date_commande', '>=', $startOfYear)
            ->whereDate('points_livreurs.date_commande', '<=', $today)
            ->selectRaw("CONCAT(COALESCE(u.nom,''), ' ', COALESCE(u.prenoms,'')) as label, SUM(points_livreurs.gain_jour) as total")
            ->groupBy('u.id', 'u.nom', 'u.prenoms')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $repartitionGainsLivreursLabels = $topGainsLivreurs->pluck('label')->map(fn ($v) => trim((string) $v) ?: 'N/A')->values()->all();
        $repartitionGainsLivreursData = $topGainsLivreurs->pluck('total')->map(fn ($v) => (int) $v)->values()->all();
        $totalGainsLivreursAnnee = (int) PointsLivreur::query()
            ->whereDate('date_commande', '>=', $startOfYear)
            ->whereDate('date_commande', '<=', $today)
            ->sum('gain_jour');
        $autreGainsLivreurs = $totalGainsLivreursAnnee - array_sum($repartitionGainsLivreursData);
        if ($autreGainsLivreurs > 0) {
            $repartitionGainsLivreursLabels[] = 'Autre';
            $repartitionGainsLivreursData[] = (int) $autreGainsLivreurs;
        }

        $topDepensesLivreurs = PointsLivreur::query()
            ->join('utilisateurs as u', 'points_livreurs.utilisateur_id', '=', 'u.id')
            ->whereDate('points_livreurs.date_commande', '>=', $startOfYear)
            ->whereDate('points_livreurs.date_commande', '<=', $today)
            ->selectRaw("CONCAT(COALESCE(u.nom,''), ' ', COALESCE(u.prenoms,'')) as label, SUM(points_livreurs.depense) as total")
            ->groupBy('u.id', 'u.nom', 'u.prenoms')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $repartitionDepensesLivreursLabels = $topDepensesLivreurs->pluck('label')->map(fn ($v) => trim((string) $v) ?: 'N/A')->values()->all();
        $repartitionDepensesLivreursData = $topDepensesLivreurs->pluck('total')->map(fn ($v) => (int) $v)->values()->all();
        $totalDepensesLivreursAnnee = (int) PointsLivreur::query()
            ->whereDate('date_commande', '>=', $startOfYear)
            ->whereDate('date_commande', '<=', $today)
            ->sum('depense');
        $autreDepensesLivreurs = $totalDepensesLivreursAnnee - array_sum($repartitionDepensesLivreursData);
        if ($autreDepensesLivreurs > 0) {
            $repartitionDepensesLivreursLabels[] = 'Autre';
            $repartitionDepensesLivreursData[] = (int) $autreDepensesLivreurs;
        }

        return view('dashboard', compact(
            'nbColisRecusAnnee',
            'nbColisLivresAnnee',
            'nbColisNonLivresAnnee',
            'nbColisRetoursAnnee',
            'nbFacturesTotal',
            'nbFacturesBrouillon',
            'nbFacturesValidees',
            'nbFacturesPayees',
            'montantFacturesValideesAnnee',
            'montantFacturesTotal',
            'montantDettesTotal',
            'montantDettesRembourseesTotal',
            'montantDettesRestantTotal',
            'montantLivraisonsValideesAnnee',
            'revenusTotalAnnee',
            'chargesVariablesAnnee',
            'chargesFixesAnnee',
            'epargnesAnnee',
            'paiementLivreursAnnee',
            'gainSyntheseAnnee',
            'nbColisLivresMois',
            'montantLivraisonsPayeesMois',
            'montantFacturesPayeesMois',
            'depensesMois',
            'gainMois',
            'totalPaieMontantPaye',
            'nbFichesPayees',
            'nbFichesAPayer',
            'nbPeriodesPayees',
            'nbPeriodesEnCours',
            'repartitionBoutiquesLabels',
            'repartitionBoutiquesData',
            'repartitionClientsLabels',
            'repartitionClientsData',
            'repartitionGainsLivreursLabels',
            'repartitionGainsLivreursData',
            'repartitionDepensesLivreursLabels',
            'repartitionDepensesLivreursData'
        ));
    }
}

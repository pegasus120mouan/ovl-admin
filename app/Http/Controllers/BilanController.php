<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\PointsLivreur;
use App\Models\Utilisateur;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BilanController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        // Commandes du jour
        $commandesJour = Commande::with(['client.boutique', 'livreur'])
            ->whereDate('date_reception', $date)
            ->get();
        
        // Commandes livrées du jour
        $commandesLivrees = Commande::with(['client.boutique', 'livreur'])
            ->whereDate('date_livraison', $date)
            ->where('statut', 'Livré')
            ->get();
        
        // Commandes retournées du jour
        $commandesRetour = Commande::whereDate('date_retour', $date)
            ->where('statut', 'Retour')
            ->get();
        
        // Statistiques
        $totalCommandes = $commandesJour->count();
        $totalLivrees = $commandesLivrees->count();
        $totalRetour = $commandesRetour->count();
        $totalNonLivrees = Commande::where('statut', 'Non Livré')->count();
        
        // Montants
        $totalCoutGlobal = $commandesJour->sum('cout_global');
        $totalCoutLivraison = $commandesJour->sum('cout_livraison');
        $totalCoutReel = $commandesJour->sum('cout_reel');
        
        // Montants livrés
        $montantLivre = $commandesLivrees->sum('cout_global');
        $livraisonLivre = $commandesLivrees->sum('cout_livraison');
        $coutReelLivre = $commandesLivrees->sum('cout_reel');
        
        // Livreurs actifs du jour
        $livreursActifs = Utilisateur::livreurs()
            ->whereHas('commandesLivreur', function($q) use ($date) {
                $q->whereDate('date_reception', $date);
            })
            ->withCount(['commandesLivreur as commandes_jour' => function($q) use ($date) {
                $q->whereDate('date_reception', $date);
            }])
            ->get();
        
        // Points par clients (basé sur les commandes livrées aujourd'hui)
        $pointsClients = $commandesLivrees->groupBy('utilisateur_id')->map(function($commandes) use ($commandesJour) {
            $client = $commandes->first()->client;
            $utilisateurId = $commandes->first()->utilisateur_id;
            $commandesClientJour = $commandesJour->where('utilisateur_id', $utilisateurId);
            return [
                'client' => $client,
                'cout_global' => $commandes->sum('cout_global'),
                'cout_livraison' => $commandes->sum('cout_livraison'),
                'cout_reel' => $commandes->sum('cout_reel'),
                'nbre_recu' => $commandesClientJour->count(),
                'nbre_livre' => $commandes->count(),
                'nbre_non_livre' => $commandesClientJour->where('statut', 'Non Livré')->count(),
            ];
        });
        
        // Versement par livreur (basé sur les commandes livrées aujourd'hui)
        $versements = $commandesLivrees->groupBy('livreur_id')->map(function($commandes) use ($date) {
            $livreur = $commandes->first()->livreur;
            $montantGlobal = $commandes->sum('cout_global');
            
            // Récupérer les dépenses du livreur pour aujourd'hui
            $pointLivreur = PointsLivreur::where('utilisateur_id', $livreur->id ?? null)
                ->whereDate('date_commande', $date)
                ->first();
            $depenses = $pointLivreur ? $pointLivreur->depense : 0;
            
            return [
                'livreur' => $livreur,
                'montant_global' => $montantGlobal,
                'depenses' => $depenses,
                'montant_remettre' => $montantGlobal - $depenses,
            ];
        })->filter(function($item) {
            return $item['livreur'] !== null;
        });
        
        // Point livreur (basé sur les commandes livrées aujourd'hui)
        $pointLivreurs = $commandesLivrees->groupBy('livreur_id')->map(function($commandes) use ($date) {
            $livreur = $commandes->first()->livreur;
            $recette = $commandes->sum('cout_livraison');
            
            // Récupérer les dépenses du livreur pour aujourd'hui
            $pointLivreur = PointsLivreur::where('utilisateur_id', $livreur->id ?? null)
                ->whereDate('date_commande', $date)
                ->first();
            $depense = $pointLivreur ? $pointLivreur->depense : 0;
            
            return [
                'livreur' => $livreur,
                'recette' => $recette,
                'depense' => $depense,
                'gain' => $recette - $depense,
            ];
        })->filter(function($item) {
            return $item['livreur'] !== null;
        });
        
        return view('bilans.index', compact(
            'date',
            'commandesJour',
            'commandesLivrees',
            'commandesRetour',
            'totalCommandes',
            'totalLivrees',
            'totalRetour',
            'totalNonLivrees',
            'totalCoutGlobal',
            'totalCoutLivraison',
            'totalCoutReel',
            'montantLivre',
            'livraisonLivre',
            'coutReelLivre',
            'livreursActifs',
            'pointsClients',
            'versements',
            'pointLivreurs'
        ));
    }

    public function sendClientReportSms(Request $request, Utilisateur $client)
    {
        if (($client->role ?? null) !== 'clients') {
            abort(404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        if (!($client->contact ?? null)) {
            return redirect()
                ->back()
                ->with('error', "Aucun contact pour ce client");
        }

        $date = $validated['date'];

        $totalColis = Commande::query()
            ->where('utilisateur_id', $client->id)
            ->whereDate('date_reception', $date)
            ->count();

        $livres = Commande::query()
            ->where('utilisateur_id', $client->id)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', $date)
            ->count();

        $nonLivres = Commande::query()
            ->where('utilisateur_id', $client->id)
            ->where('statut', 'Non Livré')
            ->whereDate('date_reception', $date)
            ->count();

        $montant = Commande::query()
            ->where('utilisateur_id', $client->id)
            ->where('statut', 'Livré')
            ->whereDate('date_livraison', $date)
            ->sum('cout_reel');

        $boutiqueNom = $client->boutique->nom ?? $client->nom;
        $clientNom = trim(($client->nom ?? '') . ' ' . ($client->prenoms ?? ''));

        $nl = "\r\n";

        $message = $boutiqueNom . $nl . $nl
            . "Bonjour {$clientNom}," . $nl . $nl
            . "Voici votre rapport de livraison :" . $nl
            . "Date: " . \Carbon\Carbon::parse($date)->format('d/m/Y') . $nl
            . "Total colis: {$totalColis}" . $nl
            . "Livres: {$livres}" . $nl
            . "Non livres: {$nonLivres}" . $nl
            . "Montant: " . number_format((float) $montant, 0, ',', ' ') . " CFA" . $nl . $nl
            . "Un point détaillé vous sera transmis par Whatsapp" . $nl . $nl
            . "Merci de votre confiance." . $nl
            . "OVL Delivery";

        try {
            SmsService::sendMessage($client->contact, $message);
        } catch (\Throwable $e) {
            \Log::error('Bilan client SMS failed', [
                'client_id' => $client->id,
                'contact' => $client->contact,
                'date' => $date,
                'message' => $e->getMessage(),
            ]);
            return redirect()
                ->back()
                ->with('error', "Echec d'envoi du SMS: " . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'SMS envoy\xC3\xA9 avec succ\xC3\xA8s');
    }
    
    public function hier()
    {
        // Utiliser la date d'hier
        $date = Carbon::yesterday()->format('Y-m-d');
        
        // Commandes du jour (hier)
        $commandesJour = Commande::with(['client.boutique', 'livreur'])
            ->whereDate('date_reception', $date)
            ->get();
        
        // Commandes livrées du jour (hier)
        $commandesLivrees = Commande::with(['client.boutique', 'livreur'])
            ->whereDate('date_livraison', $date)
            ->where('statut', 'Livré')
            ->get();
        
        // Commandes retournées du jour (hier)
        $commandesRetour = Commande::whereDate('date_retour', $date)
            ->where('statut', 'Retour')
            ->get();
        
        // Statistiques
        $totalCommandes = $commandesJour->count();
        $totalLivrees = $commandesLivrees->count();
        $totalRetour = $commandesRetour->count();
        $totalNonLivrees = $commandesJour->where('statut', 'Non Livré')->count();
        
        // Montants
        $totalCoutGlobal = $commandesJour->sum('cout_global');
        $totalCoutLivraison = $commandesJour->sum('cout_livraison');
        $totalCoutReel = $commandesJour->sum('cout_reel');
        
        // Montants livrés
        $montantLivre = $commandesLivrees->sum('cout_global');
        $livraisonLivre = $commandesLivrees->sum('cout_livraison');
        $coutReelLivre = $commandesLivrees->sum('cout_reel');
        
        // Livreurs actifs du jour (hier)
        $livreursActifs = Utilisateur::livreurs()
            ->whereHas('commandesLivreur', function($q) use ($date) {
                $q->whereDate('date_reception', $date);
            })
            ->withCount(['commandesLivreur as commandes_jour' => function($q) use ($date) {
                $q->whereDate('date_reception', $date);
            }])
            ->get();
        
        // Points par clients (basé sur les commandes livrées hier)
        $pointsClients = $commandesLivrees->groupBy('utilisateur_id')->map(function($commandes) use ($commandesJour) {
            $client = $commandes->first()->client;
            $utilisateurId = $commandes->first()->utilisateur_id;
            $commandesClientJour = $commandesJour->where('utilisateur_id', $utilisateurId);
            return [
                'client' => $client,
                'cout_global' => $commandes->sum('cout_global'),
                'cout_livraison' => $commandes->sum('cout_livraison'),
                'cout_reel' => $commandes->sum('cout_reel'),
                'nbre_recu' => $commandesClientJour->count(),
                'nbre_livre' => $commandes->count(),
                'nbre_non_livre' => $commandesClientJour->where('statut', 'Non Livré')->count(),
            ];
        });
        
        // Versement par livreur (basé sur les commandes livrées hier)
        $versements = $commandesLivrees->groupBy('livreur_id')->map(function($commandes) use ($date) {
            $livreur = $commandes->first()->livreur;
            $montantGlobal = $commandes->sum('cout_global');
            
            $pointLivreur = PointsLivreur::where('utilisateur_id', $livreur->id ?? null)
                ->whereDate('date_commande', $date)
                ->first();
            $depenses = $pointLivreur ? $pointLivreur->depense : 0;
            
            return [
                'livreur' => $livreur,
                'montant_global' => $montantGlobal,
                'depenses' => $depenses,
                'montant_remettre' => $montantGlobal - $depenses,
            ];
        })->filter(function($item) {
            return $item['livreur'] !== null;
        });
        
        // Point livreur (basé sur les commandes livrées hier)
        $pointLivreurs = $commandesLivrees->groupBy('livreur_id')->map(function($commandes) use ($date) {
            $livreur = $commandes->first()->livreur;
            $recette = $commandes->sum('cout_livraison');
            
            $pointLivreur = PointsLivreur::where('utilisateur_id', $livreur->id ?? null)
                ->whereDate('date_commande', $date)
                ->first();
            $depense = $pointLivreur ? $pointLivreur->depense : 0;
            
            return [
                'livreur' => $livreur,
                'recette' => $recette,
                'depense' => $depense,
                'gain' => $recette - $depense,
            ];
        })->filter(function($item) {
            return $item['livreur'] !== null;
        });
        
        return view('bilans.index', compact(
            'date',
            'commandesJour',
            'commandesLivrees',
            'commandesRetour',
            'totalCommandes',
            'totalLivrees',
            'totalRetour',
            'totalNonLivrees',
            'totalCoutGlobal',
            'totalCoutLivraison',
            'totalCoutReel',
            'montantLivre',
            'livraisonLivre',
            'coutReelLivre',
            'livreursActifs',
            'pointsClients',
            'versements',
            'pointLivreurs'
        ));
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\BilanController;
use App\Http\Controllers\PointsLivreurController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\CoutLivraisonController;
use App\Http\Controllers\PointsClientController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\PrixController;

// Routes d'authentification
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (protégé)
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

// Commandes
Route::resource('commandes', CommandeController::class);
Route::get('commandes-livrees', [CommandeController::class, 'livrees'])->name('commandes.livrees');
Route::get('commandes-non-livrees', [CommandeController::class, 'nonLivrees'])->name('commandes.non-livrees');
Route::get('commandes-print', [CommandeController::class, 'print'])->name('commandes.print');
Route::patch('commandes/{commande}/marquer-livre', [CommandeController::class, 'marquerLivre'])->name('commandes.marquer-livre');
Route::patch('commandes/{commande}/marquer-retour', [CommandeController::class, 'marquerRetour'])->name('commandes.marquer-retour');

// Bilans
Route::get('bilans', [BilanController::class, 'index'])->name('bilans.index');
Route::get('bilans/hier', [BilanController::class, 'hier'])->name('bilans.hier');
Route::post('bilans/{client}/envoyer-sms', [BilanController::class, 'sendClientReportSms'])->name('bilans.send-client-sms');

// Points Livreurs
Route::get('points-livreurs', [PointsLivreurController::class, 'index'])->name('points-livreurs.index');
Route::get('points-livreurs/print-depot', [PointsLivreurController::class, 'printDepot'])->name('points-livreurs.print-depot');
Route::post('points-livreurs', [PointsLivreurController::class, 'store'])->name('points-livreurs.store');
Route::put('points-livreurs/{pointsLivreur}', [PointsLivreurController::class, 'update'])->name('points-livreurs.update');
Route::delete('points-livreurs/{pointsLivreur}', [PointsLivreurController::class, 'destroy'])->name('points-livreurs.destroy');
Route::post('points-livreurs/sync-recettes', [PointsLivreurController::class, 'syncRecettes'])->name('points-livreurs.sync-recettes');

// Points Clients
Route::get('points-clients', [PointsClientController::class, 'index'])->name('points-clients.index');
Route::get('points-clients/print', [PointsClientController::class, 'print'])->name('points-clients.print');

Route::get('cout-livraisons', [CoutLivraisonController::class, 'indexWeb'])->name('cout-livraisons.index');
Route::post('cout-livraisons', [CoutLivraisonController::class, 'storeWeb'])->name('cout-livraisons.store');
Route::put('cout-livraisons/{coutLivraison}', [CoutLivraisonController::class, 'updateWeb'])->name('cout-livraisons.update');
Route::delete('cout-livraisons/{coutLivraison}', [CoutLivraisonController::class, 'destroyWeb'])->name('cout-livraisons.destroy');

// Communes
Route::get('communes', [CommuneController::class, 'indexWeb'])->name('communes.index');
Route::post('communes', [CommuneController::class, 'storeWeb'])->name('communes.store');
Route::put('communes/{commune}', [CommuneController::class, 'updateWeb'])->name('communes.update');
Route::delete('communes/{commune}', [CommuneController::class, 'destroyWeb'])->name('communes.destroy');

// Prix par commune
Route::get('communes/{commune}/prix', [PrixController::class, 'indexWeb'])->name('communes.prix.index');
Route::get('communes/{commune}/prix/print', [PrixController::class, 'printWeb'])->name('communes.prix.print');
Route::post('communes/{commune}/prix', [PrixController::class, 'storeWeb'])->name('communes.prix.store');
Route::put('communes/{commune}/prix/{prix}', [PrixController::class, 'updateWeb'])->name('communes.prix.update');
Route::delete('communes/{commune}/prix/{prix}', [PrixController::class, 'destroyWeb'])->name('communes.prix.destroy');

// Zones
Route::get('communes/zones', [ZoneController::class, 'indexWeb'])->name('communes.zones');
Route::post('communes/zones', [ZoneController::class, 'storeWeb'])->name('communes.zones.store');
Route::put('communes/zones/{zone}', [ZoneController::class, 'updateWeb'])->name('communes.zones.update');
Route::delete('communes/zones/{zone}', [ZoneController::class, 'destroyWeb'])->name('communes.zones.destroy');

Route::get('communes/zones/{zone}/prix', [ZoneController::class, 'prixIndexWeb'])->name('communes.zones.prix.index');
Route::post('communes/zones/{zone}/prix/attach', [ZoneController::class, 'attachCommuneWeb'])->name('communes.zones.prix.attach');
Route::delete('communes/zones/{zone}/prix/detach/{commune}', [ZoneController::class, 'detachCommuneWeb'])->name('communes.zones.prix.detach');
Route::post('communes/zones/{zone}/prix', [ZoneController::class, 'prixStoreWeb'])->name('communes.zones.prix.store');
Route::put('communes/zones/{zone}/prix/{prix}', [ZoneController::class, 'prixUpdateWeb'])->name('communes.zones.prix.update');
Route::delete('communes/zones/{zone}/prix/{prix}', [ZoneController::class, 'prixDestroyWeb'])->name('communes.zones.prix.destroy');

// Clients
Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
Route::post('clients/{client}/renvoyer-sms', [ClientController::class, 'resendSms'])->name('clients.resend-sms');

Route::post('clients/verify-pin', [UtilisateurController::class, 'verifyPin'])->name('clients.verify-pin');
Route::post('clients/resend-pin', [UtilisateurController::class, 'resendPin'])->name('clients.resend-pin');

Route::get('boutiques', [BoutiqueController::class, 'index'])->name('boutiques.index');
Route::post('boutiques', [BoutiqueController::class, 'store'])->name('boutiques.store');
Route::get('boutiques/{boutique}', [BoutiqueController::class, 'show'])->name('boutiques.show');
Route::put('boutiques/{boutique}', [BoutiqueController::class, 'update'])->name('boutiques.update');

Route::get('users/administrateurs', [UtilisateurController::class, 'administrateurs'])->name('users.administrateurs');
Route::get('users/livreurs', [UtilisateurController::class, 'livreurs'])->name('users.livreurs');

Route::post('users/administrateurs', [UtilisateurController::class, 'storeAdministrateurWeb'])->name('users.administrateurs.store');
Route::post('users/livreurs', [UtilisateurController::class, 'storeLivreurWeb'])->name('users.livreurs.store');

Route::get('users/administrateurs/{admin}', [UtilisateurController::class, 'showAdministrateurWeb'])->name('users.administrateurs.show');

Route::get('users/livreurs/{livreur}', [UtilisateurController::class, 'showLivreurWeb'])->name('users.livreurs.show');

Route::get('users/livreurs/{livreur}/commandes', [UtilisateurController::class, 'commandesLivreurWeb'])->name('users.livreurs.commandes');

Route::put('users/administrateurs/{admin}', [UtilisateurController::class, 'updateAdministrateurWeb'])->name('users.administrateurs.update');
Route::put('users/livreurs/{livreur}', [UtilisateurController::class, 'updateLivreurWeb'])->name('users.livreurs.update');
Route::delete('users/administrateurs/{admin}', [UtilisateurController::class, 'destroyAdministrateurWeb'])->name('users.administrateurs.destroy');
Route::delete('users/livreurs/{livreur}', [UtilisateurController::class, 'destroyLivreurWeb'])->name('users.livreurs.destroy');

Route::patch('users/administrateurs/{admin}/toggle-statut', [UtilisateurController::class, 'toggleAdministrateurStatutWeb'])->name('users.administrateurs.toggle-statut');
Route::patch('users/livreurs/{livreur}/toggle-statut', [UtilisateurController::class, 'toggleLivreurStatutWeb'])->name('users.livreurs.toggle-statut');

Route::get('users/gestion-statuts', [UtilisateurController::class, 'gestionStatutsWeb'])->name('users.gestion-statuts');
Route::patch('users/{utilisateur}/toggle-statut', [UtilisateurController::class, 'toggleStatutWeb'])->name('users.toggle-statut');

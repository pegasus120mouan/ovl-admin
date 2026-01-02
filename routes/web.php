<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\BilanController;
use App\Http\Controllers\PointsLivreurController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\UtilisateurController;

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

// Points Livreurs
Route::get('points-livreurs', [PointsLivreurController::class, 'index'])->name('points-livreurs.index');
Route::post('points-livreurs', [PointsLivreurController::class, 'store'])->name('points-livreurs.store');
Route::put('points-livreurs/{pointsLivreur}', [PointsLivreurController::class, 'update'])->name('points-livreurs.update');
Route::delete('points-livreurs/{pointsLivreur}', [PointsLivreurController::class, 'destroy'])->name('points-livreurs.destroy');
Route::post('points-livreurs/sync-recettes', [PointsLivreurController::class, 'syncRecettes'])->name('points-livreurs.sync-recettes');

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

Route::put('users/administrateurs/{admin}', [UtilisateurController::class, 'updateAdministrateurWeb'])->name('users.administrateurs.update');
Route::delete('users/administrateurs/{admin}', [UtilisateurController::class, 'destroyAdministrateurWeb'])->name('users.administrateurs.destroy');

Route::patch('users/administrateurs/{admin}/toggle-statut', [UtilisateurController::class, 'toggleAdministrateurStatutWeb'])->name('users.administrateurs.toggle-statut');

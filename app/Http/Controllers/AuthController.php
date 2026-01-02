<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
        return view('dashboard');
    }
}

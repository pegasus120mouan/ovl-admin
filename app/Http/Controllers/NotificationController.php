<?php

namespace App\Http\Controllers;

use App\Models\NotificationNumero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function index()
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $numeros = NotificationNumero::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('notifications.index', compact('numeros'));
    }

    public function store(Request $request)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'telephone' => 'required|string|max:20',
        ]);

        $telephone = $this->normalizePhone($validated['telephone']);

        if (NotificationNumero::where('telephone', $telephone)->exists()) {
            return redirect()
                ->route('notifications.index')
                ->withInput()
                ->with('error', 'Ce numéro est déjà enregistré.');
        }

        NotificationNumero::create([
            'telephone' => $telephone,
            'created_at' => now(),
        ]);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Numéro enregistré avec succès.');
    }

    public function destroy(NotificationNumero $notificationNumero)
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $notificationNumero->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Numéro supprimé avec succès.');
    }

    private function normalizePhone(string $telephone): string
    {
        $telephone = preg_replace('/\s+/', '', $telephone);
        $telephone = ltrim($telephone, '+');

        if (str_starts_with($telephone, '0') && strlen($telephone) === 10) {
            $telephone = '225' . $telephone;
        }

        return $telephone;
    }
}

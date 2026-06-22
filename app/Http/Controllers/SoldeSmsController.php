<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Support\Facades\Session;

class SoldeSmsController extends Controller
{
    public function index()
    {
        if (!Session::has('utilisateur')) {
            return redirect()->route('login');
        }

        $error = null;
        $balance = null;

        try {
            $balance = SmsService::checkBalance();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('solde_sms.index', compact('balance', 'error'));
    }
}

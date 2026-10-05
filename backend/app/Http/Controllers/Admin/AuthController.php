<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\View\View;

/**
 * Staff login + two-factor step (design: AdminLogin).
 * Static export: no real authentication. "Login" reveals the 2FA step,
 * "Verify & continue" links to the dashboard. Wire to Laravel auth later.
 */
class AuthController extends Controller
{
    public function login(): View
    {
        return view('admin.auth.login', [
            'otpPhone' => DemoData::currentUser()['phone_masked'],
            'otpDigits' => ['4', '8', '2', '9', '', ''],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmploymentContract;
use App\Models\KycVerification;
use App\Models\OnboardingInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function doLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('portal.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $profile = EmployeeProfile::with(['branch', 'position'])
            ->where('user_id', $user->id)
            ->first();

        // Find latest invite by user's email
        $invite = OnboardingInvite::with(['position', 'branch'])
            ->where('email', $user->email)
            ->latest('id')
            ->first();

        $latestKyc = null;
        $latestContract = null;
        if ($invite) {
            $latestKyc = KycVerification::where('onboarding_invite_id', $invite->id)
                ->latest('id')
                ->first();
            $latestContract = EmploymentContract::where('onboarding_invite_id', $invite->id)
                ->latest('id')
                ->first();
        }

        return view('portal.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'invite' => $invite,
            'latestKyc' => $latestKyc,
            'latestContract' => $latestContract,
        ]);
    }
}



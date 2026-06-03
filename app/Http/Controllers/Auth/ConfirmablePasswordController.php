<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ SESUAIKAN: Gunakan login field yang sesuai (email atau username)
        $user = $request->user();

        // Tentukan field login berdasarkan user (email atau username)
        $loginField = $user->username ? 'username' : 'email';
        $loginValue = $loginField === 'username' ? $user->username : $user->email;

        if (! Auth::guard('web')->validate([
            $loginField => $loginValue,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

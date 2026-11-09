<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function registerForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->role === 'customer'
                ? redirect()->route('customer.dashboard')
                : abort(403);
        }

        return view('customer.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with('success', 'Your account has been created.');
    }

    public function loginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->role === 'customer'
                ? redirect()->route('customer.dashboard')
                : abort(403);
        }

        return view('customer.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            abort(403);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'role' => 'customer'])) {
            throw ValidationException::withMessages([
                'email' => 'The provided customer credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->role === 'customer',
            403
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

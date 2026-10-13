<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        // 1. Validar que llegue el campo 'login' y 'password'
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Determinar si es un correo o un nombre de usuario
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 3. Intentar el inicio de sesión
        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // 4. Si falla, regresar con error
        throw ValidationException::withMessages([
            'login' => __('auth.failed'),
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

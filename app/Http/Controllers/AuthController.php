<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // --- LOGIN ---
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->flash('welcome_user', Auth::user()->name);
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // --- REGISTRATION ---
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/dashboard')->with('success', '¡Registro exitoso! Bienvenido.');
    }

    // --- PASSWORD RECOVERY ---
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = \App\Models\User::where('email', $request->email)->first();
        
        // Generate 6-digit code
        $code = rand(100000, 999999);
        
        $user->verification_code = $code;
        $user->verification_code_expires_at = now()->addMinutes(15);
        $user->save();

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationCode($code));
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error al enviar el correo: ' . $e->getMessage()]);
        }

        return redirect()->route('password.reset.show', ['email' => $user->email])
            ->with('success', 'Código enviado a tu correo.');
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Validate Code
        if ($user->verification_code !== $request->code) {
            return back()->withErrors(['code' => 'El código es incorrecto.']);
        }

        // Validate Expiration
        if (now()->greaterThan($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'El código ha expirado. Solicita uno nuevo.']);
        }

        // Reset Password
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Contraseña restablecida exitosamente.');
    }
}

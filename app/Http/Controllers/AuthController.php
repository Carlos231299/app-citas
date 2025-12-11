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
            'password' => [
                'required', 
                'confirmed', 
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        // Send Welcome Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($user->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Welcome Email Error: ' . $e->getMessage());
        }

        // Redirect to Login (No Auto-Login)
        return redirect()->route('login')->with('success', '¡Registro exitoso! Por favor inicia sesión.');
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

        // Redirect to Step 2: Verify Code
        return redirect()->route('password.verify.show', ['email' => $user->email])
            ->with('success', 'Código enviado. Por favor revísalo.');
    }

    // Step 2: Show Code Form
    public function showVerifyCode(Request $request)
    {
        return view('auth.verify-code', ['email' => $request->email]);
    }

    // Step 2: Process Code
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string'
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Validate Code & Expiry
        if ($user->verification_code !== $request->code) {
            return back()->withErrors(['code' => 'El código es incorrecto.']);
        }

        if (now()->greaterThan($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'El código ha expirado. Solicita uno nuevo.']);
        }

        // Success: Show Step 3 (Reset Password Form) passing validated data
        // Flash the success message for SweetAlert to pick up
        session()->flash('success', 'Código verificado exitosamente. Ingrese su nueva contraseña.');

        return view('auth.reset-password', [
            'email' => $request->email,
            'code' => $request->code
        ]);
    }

    // Step 3 is just the POST action now (View is rendered by verifyCode)
    // kept for fallback if needed, but primarily verifyCode renders the view directly
    public function showResetPassword(Request $request)
    {
        // Fallback: If accessed directly without code, redirect to start
        if (!$request->has('code')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password', ['email' => $request->email, 'code' => $request->code]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
            'password' => [
                'required', 
                'confirmed', 
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Double Check (Security)
        if ($user->verification_code !== $request->code || now()->greaterThan($user->verification_code_expires_at)) {
            return redirect()->route('password.request')->withErrors(['email' => 'La sesión de recuperación ha expirado.']);
        }

        // Reset Password
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();

        // No Auto-Login
        return redirect()->route('login')->with('success', 'Contraseña actualizada. Inicia sesión.');
    }
}

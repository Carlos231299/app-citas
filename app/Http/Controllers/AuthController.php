<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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
        $user->verification_code_expires_at = now()->addMinutes(5);
        $user->save();

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationCode($code));
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error al enviar el correo: ' . $e->getMessage()]);
        }

        // Redirect to Step 2: Verify Code
        return redirect()->route('password.verify.show', ['email' => $user->email])
            ->with('success', 'Código enviado. Expira en 5 minutos.');
    }

    // Step 2: Show Code Form
    public function showVerifyCode(Request $request)
    {
        return view('auth.verify-code', ['email' => $request->email]);
    }

    // Resend Code Action
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, string $token) {
                 // Custom Mail Logic if needed, or use default Notifiable
                 // But we want to use our custom view:
                 $url = route('password.reset', ['token' => $token, 'email' => $user->email]);
                 Mail::send('emails.reset-link', ['url' => $url, 'user' => $user], function ($m) use ($user) {
                     $m->to($user->email)->subject('Restablecer Contraseña - Barbería JR');
                 });
            }
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', '¡Enlacé de recuperación enviado! Revisa tu correo.');
        }

        return back()->withErrors(['email' => 'No podemos encontrar un usuario con ese correo electrónico.']);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required', 
                'confirmed', 
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new \Illuminate\Auth\Events\PasswordReset($user));
                
                // Send Confirmation Email
                try {
                    Mail::send('emails.password-changed', ['user' => $user], function ($m) use ($user) {
                        $m->to($user->email)->subject('Contraseña Actualizada - Barbería JR');
                    });
                } catch (\Exception $e) {
                    // Log error but don't fail the request
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', '¡Tu contraseña ha sido restablecida! Te hemos enviado un correo de confirmación.');
        }

        return back()->withErrors(['email' => 'El token de reestablecimiento es inválido o el correo no coincide.']);
    }
}

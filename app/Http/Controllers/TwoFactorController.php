<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCode;

class TwoFactorController extends Controller
{
    public function index()
    {
        if (!session()->has('2fa:user_id')) {
            return redirect('login');
        }
        
        $userId = session('2fa:user_id');
        $user = User::findOrFail($userId);
        
        // Obfuscate email
        $email = $user->email;
        $parts = explode('@', $email);
        $obfuscated = substr($parts[0], 0, 2) . '****@' . $parts[1];

        return view('auth.two-factor', ['email' => $obfuscated]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|integer',
        ]);

        if (!session()->has('2fa:user_id')) {
            return redirect('login');
        }

        $userId = session('2fa:user_id');
        $user = User::findOrFail($userId);

        if ($user->two_factor_code == $request->code && $user->two_factor_expires_at > now()) {
            
            // Clear Codes
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            
            // SET TRUSTED DEVICE (Adaptive 2FA)
            $token = \Illuminate\Support\Str::uuid()->toString();
            $user->device_token = $token;
            $user->save();
            
            // Queue Cookie for 30 days (43200 minutes)
            \Illuminate\Support\Facades\Cookie::queue('trusted_device', $token, 43200);
            
            // Login
            Auth::login($user);
            session()->forget('2fa:user_id');
            session()->regenerate();
            session()->flash('welcome_user', $user->name);

            return redirect()->intended('dashboard');
        }


        return back()->withErrors(['code' => 'El código es inválido o ha expirado.']);
    }

    public function resend()
    {
        if (!session()->has('2fa:user_id')) {
            return redirect('login');
        }

        $userId = session('2fa:user_id');
        $user = User::findOrFail($userId);

        $code = rand(100000, 999999);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(new VerificationCode($code));
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'Error enviando correo: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Código reenviado exitosamente.');
    }
}

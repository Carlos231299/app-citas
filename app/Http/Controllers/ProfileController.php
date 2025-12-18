<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'nullable|string',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => [
                'nullable', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        // Check current password if changing psd
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $validated['name'];
        if ($user->id !== 1) { // Protect Admin Username
             $user->username = $validated['username'];
        }
        $user->email = $validated['email'];
        if(isset($validated['avatar'])) {
            $user->avatar = $validated['avatar'];
        }
        $user->save();

        // ---------------------------------------------------------
        // BARBER STATUS UPDATES (If user is a barber)
        // ---------------------------------------------------------
        if ($user->barber) {
            $barber = $user->barber;
            
            // Validate Barber specific fields
            $barberData = $request->validate([
                'whatsapp_number' => 'nullable|string|max:20',
                'is_active' => 'sometimes|boolean',
                'unavailable_start' => 'nullable|date|required_if:is_active,0|after_or_equal:yesterday',
                'unavailable_end' => 'nullable|date|after_or_equal:unavailable_start|required_if:is_active,0',
                'special_mode' => 'sometimes|boolean',
                'extra_time_start' => 'nullable|date|required_if:special_mode,1|after_or_equal:yesterday', 
                'extra_time_end' => 'nullable|date|after_or_equal:extra_time_start|required_if:special_mode,1',
            ]);

            // Logic from BarberController:
            // If turning Active ON explicitly (via switch), clear unavailability logic if not set
            if ($request->has('is_active') && $request->boolean('is_active')) {
                if (!$request->has('unavailable_start')) {
                    $barberData['unavailable_start'] = null;
                    $barberData['unavailable_end'] = null;
                }
            }
            
            // If turning Active OFF, turn Special Mode OFF
            if ($request->has('is_active') && !$request->boolean('is_active')) {
                $barberData['special_mode'] = false;
            }

            // DEBUG LOGGING
            \Illuminate\Support\Facades\Log::info('ProfileUpdate PRE-VALIDATION', $request->all());
            
            $barber->update($barberData);
            
            // DEBUG LOGGING
            \Illuminate\Support\Facades\Log::info('ProfileUpdate POST-UPDATE', ['barber' => $barber->fresh()->toArray(), 'data' => $barberData]);
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}

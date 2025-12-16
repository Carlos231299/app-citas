<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        // STRICT CHECK: Only Admin has access to this management view.
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }
        
        $barbers = Barber::with('user')->get();
        return view('admin.barbers.index', compact('barbers'));
    }

    public function store(Request $request)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);
        
        // 1. Create User (Standard Role)
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => 'standard',
        ]);

        // 2. Create Barber
        \App\Models\Barber::create([
            'name' => $data['name'],
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'is_active' => true,
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Barbero (y Usuario) agregado exitosamente.');
    }

    public function update(Request $request, Barber $barber)
    {
        // Only Admin can update users via this controller.
        // Barbers update themselves via ProfileController.
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }
        
        $barber->load('user'); 
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
            'unavailable_start' => 'nullable|date',
            'unavailable_end' => 'nullable|date|after_or_equal:unavailable_start',
            // Restored Extra Time fields
            'special_mode' => 'sometimes|boolean',
            'extra_time_start' => 'nullable|date',
            'extra_time_end' => 'nullable|date|after_or_equal:extra_time_start',
        ];

        $validated = $request->validate($rules);

        // Clear Unavailability if setting Active = true explicitly via Switch
        // NOTE: User said "Temporary Inactivity only configures but doesn't deactivate"
        // This is likely because we need to explicitly allow is_active=1 even if dates are set, 
        // relying on AppointmentController to block dates.
        if (isset($validated['is_active']) && $validated['is_active'] == true) {
            // Only clear unavailable if we are NOT setting it in this request!
            // If we are setting unavailable_start, we definitely want it set.
            if (!isset($validated['unavailable_start'])) {
                 $validated['unavailable_start'] = null;
                 $validated['unavailable_end'] = null;
            }
        }
        
        // Handle Modal Update (Name present) vs Switch Toggle
        if ($request->has('name')) {
             // In Modal Update, we don't send switch values for special_mode/is_active usually, 
             // but if we do (hidden fields?) we might.
             // Previous logic unset them. Let's keep it safe.
             unset($validated['is_active']);
             unset($validated['special_mode']);
        } else {
             // Switch Toggle Update
             // If turning OFF is_active, also turn off special_mode
             if (isset($validated['is_active']) && $validated['is_active'] == false) {
                 $validated['special_mode'] = false;
             }
        }

        // Check for User Update (Email/Pass/Username)
        if ($request->has('name')) { // Only main edit form sends name
            $userRules = [
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|max:50|unique:users,username',
            ];
            
            // Password only if provided
            if ($request->filled('password')) {
                $userRules['password'] = 'required|string|min:8';
            }
            
            // If barber has user, ignore uniqueness check for that user
            if ($barber->user) {
                $userRules['email'] .= ',' . $barber->user->id;
                $userRules['username'] .= ',' . $barber->user->id;
            }

            $userValidated = $request->validate($userRules);

            // Update User
            if ($barber->user) {
                $userUpdateData = [
                    'name' => $request->name, // Keep name synced
                    'username' => $userValidated['username'],
                    'email' => $userValidated['email']
                ];
                if ($request->filled('password')) {
                    $userUpdateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
                }
                $barber->user->update($userUpdateData);
            } else {
                // Create New User for this Barber
                if ($request->filled(['email', 'password', 'username'])) {
                    $newUser = \App\Models\User::create([
                        'name' => $request->name,
                        'username' => $request->username,
                        'email' => $request->email,
                        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                        'role' => 'standard',
                    ]);
                    $barber->update(['user_id' => $newUser->id]);
                }
            }
        }

        $barber->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'barber' => $barber]);
        }

        // Custom success message
        return redirect()->back()->with('success', "{$barber->name} actualizado correctamente.");
    }

    public function destroy(Barber $barber)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        // Toggle active instead of hard delete typically, but user asked for delete in summary implication
        // If tied to a user, we should delete the user too to revoke access
        if ($barber->user_id) {
            \App\Models\User::where('id', $barber->user_id)->delete();
        }
        $barber->delete();
        return redirect()->back()->with('success', "{$barber->name} eliminado correctamente.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        $barbers = Barber::all();
        return view('admin.barbers.index', compact('barbers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'whatsapp_number' => 'nullable',
        ]);
        
        $data['is_active'] = true; // Default to active

        Barber::create($data);
        return redirect()->back()->with('success', 'Barbero agregado exitosamente.');
    }

    public function update(Request $request, Barber $barber)
    {
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean', // Allow direct boolean values
            'special_mode' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);

        // Logic to distinguish between HTML Form submission (where unchecked checkboxes are missing)
        // and AJAX partial update (where missing fields should be ignored).
        
        // If 'name' is present, it's a full form submission (from the Edit modal or Switch form fallback)
        if ($request->has('name')) {
             // Use boolean() to correctly interpret "0" (from hidden input) as false, 
             // and "on"/"1" as true. Missing field (unchecked checkbox) also creates false.
             $validated['is_active'] = $request->boolean('is_active');
             $validated['special_mode'] = $request->boolean('special_mode');
        } 
        // If 'name' is missing, it's a partial AJAX update. We rely on $validated containing only what was sent.
        
        // AUTO-DISABLE Logic: If is_active is being set to false, also disable special_mode
        if (isset($validated['is_active']) && $validated['is_active'] == false) {
            $validated['special_mode'] = false;
        }

        $barber->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'barber' => $barber]);
        }

        return redirect()->back()->with('success', 'Barbero actualizado correctamente.');
    }

    public function destroy(Barber $barber)
    {
        // Toggle active instead of hard delete typically, but user asked for delete in summary implication
        $barber->delete();
        return redirect()->back()->with('success', 'Barbero eliminado.');
    }
}

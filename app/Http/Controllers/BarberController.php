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
             // For HTML forms, missing checkbox means false. 
             // We explicitly check presence for these if we are in "Form Mode".
             // Note: validation cleaned them, but we need to force boolean logic for form submit.
             $validated['is_active'] = $request->has('is_active');
             $validated['special_mode'] = $request->has('special_mode');
        } 
        // If 'name' is missing, it's a partial AJAX update. We rely on $validated containing only what was sent.
        
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

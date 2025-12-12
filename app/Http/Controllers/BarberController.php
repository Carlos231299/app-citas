<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $barbers = Barber::all();
        return view('admin.barbers.index', compact('barbers'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
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
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean', // Allow direct boolean values
            'special_mode' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);

        // Logic to distinguish between HTML Form submission and AJAX partial update.
    
        // If 'name' is present, it's the Edit Profile Modal. 
        // This form DOES NOT have status switches, so we must NOT touch the status fields.
        // We unset them ensuring the DB retains its current values.
        if ($request->has('name')) {
             unset($validated['is_active']);
             unset($validated['special_mode']);
        } 
        // If 'name' is missing, it's a partial AJAX update (Switch Toggle). 
        // We rely on $validated containing the new boolean states.
        
        // AUTO-DISABLE Logic (Only for switches): If is_active is explicitly disabled, also disable special_mode
        if (isset($validated['is_active']) && $validated['is_active'] == false) {
            $validated['special_mode'] = false;
        }

        $barber->update($validated);

        if ($request->wantsJson()) {
            // Return JSON with the UPDATED object so frontend can update DOM if needed
            return response()->json(['success' => true, 'barber' => $barber]);
        }

        // Custom success message with Barber Name
        return redirect()->back()->with('success', "{$barber->name} actualizado correctamente.");
    }

    public function destroy(Barber $barber)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        // Toggle active instead of hard delete typically, but user asked for delete in summary implication
        $barber->delete();
        return redirect()->back()->with('success', "{$barber->name} eliminado correctamente.");
    }
}

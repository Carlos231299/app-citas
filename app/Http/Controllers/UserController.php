<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check role (Super Admin only? For now, 'admin')
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // List all users EXCEPT the current one
        $users = User::where('id', '!=', auth()->id())->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,standard',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,standard',
        ]);

        $payload = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $payload['password'] = Hash::make($request->password);
        }

        $user->update($payload);

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        // If user is linked to a Barber, warn?
        // Delete cascading usually happens if DB foreign keys are set, 
        // but here we just delete the user.
        // Barbers might become orphaned unless onDelete cascade is set.
        // Assuming Safe Deletion for now.

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}

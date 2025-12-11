<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'icon' => 'required|string',
        ]);

        Service::create($request->only(['name', 'description', 'price', 'icon']));

        return redirect()->back()->with('success', 'Servicio creado correctamente.');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'icon' => 'required|string',
        ]);

        $service->update($request->only(['name', 'description', 'price', 'icon']));

        return redirect()->back()->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->back()->with('success', 'Servicio eliminado correctamente.');
    }
}

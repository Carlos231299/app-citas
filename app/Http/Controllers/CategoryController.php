<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }
        $categories = Category::withCount('products')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'color' => 'required|string', // e.g. primary, info, #123456
        ]);

        Category::create($request->all());

        return redirect()->back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'required|string',
        ]);

        $category->update($request->all());

        return redirect()->back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if (trim(auth()->user()->role) !== 'admin') {
            abort(403);
        }

        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'No puedes eliminar una categoría con productos.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Categoría eliminada.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale; // Will create this model later
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        // Load products for the POS grid
        // Only active/in-stock products logic? Or all? User might want to sell something with 0 stock if they found one.
        // Let's load all for now, maybe filter by category.
        $products = Product::where('is_active', true)->get();
        $categories = \App\Models\Category::all();

        return view('admin.pos.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => 'required|string'
        ]);

        // DB Transaction to ensure detailed consistency
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Calculate total and reduce stock
            $total = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['id']);
                
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stock insuficiente para: " . $product->name);
                }

                $product->stock -= $item['qty'];
                $product->save();

                $subtotal = $product->price * $item['qty'];
                $total += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['qty'],
                    'subtotal' => $subtotal
                ];
            }

            // Create Sale Record
            // Assuming I create a Sale model. For now, I'll assume the migration will follow.
            // If Sale model doesn't exist yet, this will fail. I should create migration/model first?
            // "Precede" implies I should do it. I'll write the migration next.
            
            // Temporary: Just logging/dummy return if model missing, but I will make it.
             $sale = \App\Models\Sale::create([
                'user_id' => auth()->id(),
                'total' => $total,
                'payment_method' => $request->payment_method,
                'items' => json_encode($itemsData), // Simple JSON storage for items or use separate table? JSON is easier for simple POS.
                'completed_at' => now()
             ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['success' => true, 'message' => 'Venta realizada', 'sale_id' => $sale->id]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function history(Request $request)
    {
        $sales = $this->applyFilters(\App\Models\Sale::with('user'), $request)
                      ->orderBy('created_at', 'desc')
                      ->paginate(20)
                      ->withQueryString();

        return view('admin.pos.history', compact('sales'));
    }

    public function exportPdf(Request $request)
    {
        $sales = $this->applyFilters(\App\Models\Sale::with('user'), $request)
                      ->orderBy('created_at', 'desc')
                      ->get();
        
        $pdf = Pdf::loadView('admin.pos.pdf', compact('sales'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('reporte-ventas-' . now()->format('Y-m-d') . '.pdf');
    }

    private function applyFilters($query, $request)
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        return $query;
    }
}

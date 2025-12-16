<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    private function getFilteredAppointments(Request $request)
    {
        $status = $request->input('status', 'all');
        $barberId = $request->input('barber_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $query = Appointment::with(['service', 'barber']);

        // Status Filter
        if ($status === 'scheduled') {
            $query->where('status', 'scheduled')->where('scheduled_at', '>=', now());
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        // Barber Filter
        if ($barberId && $barberId !== 'all') {
            $query->where('barber_id', $barberId);
        }

        // Date Range Filter
        if ($dateStart) {
            $query->whereDate('scheduled_at', '>=', $dateStart);
        }
        if ($dateEnd) {
            $query->whereDate('scheduled_at', '<=', $dateEnd);
        }

        return $query->orderBy('scheduled_at', 'desc')->get();
    }

    public function pdf(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $appointments = $this->getFilteredAppointments($request);
        $total = $appointments->sum(fn($a) => $a->confirmed_price ?? $a->service->price ?? 0);
        $status = $request->input('status', 'all'); 

        $pdf = Pdf::loadView('admin.reports.pdf', compact('appointments', 'status', 'total'));
        return $pdf->download('reporte_citas_' . now()->format('Y_m_d_H_i') . '.pdf');
    }

    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $appointments = $this->getFilteredAppointments($request);
        
        // Calculate Totals using logic defined previously
        $totalEarnings = $appointments->filter(function($a) {
             return $a->status !== 'cancelled';
        })->sum(fn($a) => $a->confirmed_price ?? $a->service->price ?? 0);

        $totalAppointments = $appointments->count();
        
        if ($request->ajax()) {
            $html = view('admin.reports.partials.table_rows', compact('appointments'))->render();
            return response()->json([
                'html' => $html,
                'totalEarnings' => '$' . number_format($totalEarnings, 0, ',', '.'),
                'totalAppointments' => $totalAppointments
            ]);
        }
        
        // Data for Filters
        $barbers = \App\Models\Barber::all();
        $status = $request->input('status', 'all');

        return view('admin.reports.index', compact('appointments', 'status', 'barbers', 'totalEarnings', 'totalAppointments'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $stats = [
            'patients' => Patient::count(),
            'appointments_today' => Appointment::whereDate('scheduled_at', $today)->count(),
            'low_stock' => Product::lowStock()->where('is_active', true)->count(),
            'sales_today' => Sale::whereDate('sold_at', $today)->count(),
        ];

        $todaysAppointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('scheduled_at', $today)
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        $lowStockProducts = Product::lowStock()->where('is_active', true)->take(10)->get();

        // Finance snapshot is only for users who can view finance.
        $revenueToday = null;
        if ($user->can('finance.view')) {
            $revenueToday = (float) Payment::whereDate('paid_at', $today)->sum('amount');
        }

        return view('dashboard', compact('stats', 'todaysAppointments', 'lowStockProducts', 'revenueToday'));
    }
}

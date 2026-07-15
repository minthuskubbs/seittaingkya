<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\LowStockNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pos.use');
    }

    public function index(Request $request)
    {
        $sales = Sale::with(['patient', 'doctor'])
            ->when($request->type, fn ($q) => $q->where('sale_type', $request->type))
            ->when($request->date, fn ($q) => $q->whereDate('sold_at', $request->date))
            ->latest('sold_at')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', [
            'products' => Product::where('is_active', true)->where('type', 'medicine')->orderBy('name')->get(),
            'patients' => Patient::orderBy('name')->get(),
            'saleTypes' => \App\Models\SaleType::active()->get(),
            'doctors' => \App\Models\Doctor::where('is_active', true)->orderBy('name')->get(),
            // Optional: link the sale to a treatment so it combines into that invoice.
            'treatments' => \App\Models\Treatment::with('patient')->latest('treatment_date')->take(200)->get(),
        ]);
    }

    public function store(Request $request, LowStockNotifier $notifier)
    {
        $data = $request->validate([
            'sale_type' => ['required', \Illuminate\Validation\Rule::in(\App\Models\SaleType::pluck('slug'))],
            'patient_id' => 'nullable|exists:patients,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,kbzpay,wavepay,card,bank',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'client_uuid' => 'nullable|uuid',
        ]);

        $sale = DB::transaction(function () use ($data, $request, $notifier) {
            $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');

            $subtotal = 0;
            foreach ($data['items'] as $row) {
                $subtotal += (float) $products[$row['product_id']]->sale_price * (int) $row['quantity'];
            }
            $discount = (float) ($data['discount'] ?? 0);
            $total = max(0, $subtotal - $discount);

            $sale = Sale::create([
                'client_uuid' => $data['client_uuid'] ?? null,
                'sale_type' => $data['sale_type'],
                'patient_id' => $data['patient_id'] ?? null,
                'treatment_id' => $data['treatment_id'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'created_by' => auth()->id(),
            ]);

            $lowProducts = [];
            foreach ($data['items'] as $row) {
                $product = $products[$row['product_id']];
                $qty = (int) $row['quantity'];
                $lineTotal = (float) $product->sale_price * $qty;
                $sale->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->sale_price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ]);
                $product->adjustStock(-$qty, 'sale', $sale->sale_no);
                if ($product->isLowStock()) {
                    $lowProducts[] = $product;
                }
            }

            // Standalone sale is paid immediately at the counter. When linked to a
            // treatment, the amount is collected through the treatment's invoice
            // instead (so it isn't charged twice).
            if (empty($data['treatment_id'])) {
                $sale->payments()->create([
                    'clinic_id' => $sale->clinic_id,
                    'amount' => $total,
                    'method' => $data['payment_method'],
                    'paid_at' => now()->toDateString(),
                    'created_by' => auth()->id(),
                ]);
            }

            foreach ($lowProducts as $p) {
                $notifier->notify($p);
            }

            return $sale;
        });

        AuditLog::record('created', "Medicine sale {$sale->sale_no}", $sale);

        if ($sale->treatment_id) {
            return redirect()->route('treatments.show', $sale->treatment_id)
                ->with('status', 'Sale added to the treatment invoice.');
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Sale recorded.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['items', 'patient', 'doctor', 'payments']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items', 'patient', 'payments']);

        AuditLog::record('print', "Printed receipt {$sale->sale_no}", $sale);

        return view('sales.receipt', compact('sale'));
    }
}


<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice;
use App\Models\Council;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index()
    {
        // Financial Overview Dashboard
        $stats = [
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_invoices' => Invoice::whereIn('status', ['sent', 'draft'])->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'this_month_revenue' => Invoice::where('status', 'paid')
                                          ->whereMonth('paid_at', now()->month)
                                          ->whereYear('paid_at', now()->year)
                                          ->sum('total_amount')
        ];

        // Recent Invoices
        $recentInvoices = Invoice::with(['council', 'department'])
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get();

        // Monthly Revenue Chart Data
        $monthlyRevenue = Invoice::where('status', 'paid')
                                ->whereYear('paid_at', now()->year)
                                ->selectRaw('MONTH(paid_at) as month, SUM(total_amount) as revenue')
                                ->groupBy('month')
                                ->orderBy('month')
                                ->get();

        return view('finance.index', compact('stats', 'recentInvoices', 'monthlyRevenue'));
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with(['council', 'department']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'pending' => Invoice::whereIn('status', ['draft', 'sent'])->count(),
            'overdue' => Invoice::where('status', 'overdue')->count()
        ];

        return view('finance.invoices', compact('invoices', 'stats'));
    }

    public function createInvoice()
    {
        $councils = Council::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        
        return view('finance.create-invoice', compact('councils', 'departments'));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'due_date' => 'required|date|after:today',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'required|exists:departments,id',
            'notes' => 'nullable|string'
        ]);

        // Calculate tax and total
        $taxAmount = ($validated['amount'] * $validated['tax_rate']) / 100;
        $totalAmount = $validated['amount'] + $taxAmount;

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'due_date' => $validated['due_date'],
            'status' => Invoice::STATUS_DRAFT,
            'council_id' => $validated['council_id'],
            'department_id' => $validated['department_id'],
            'notes' => $validated['notes']
        ]);

        return redirect()->route('finance.invoices')
                        ->with('success', 'Invoice created successfully.');
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['council', 'department', 'payments']);
        
        return view('finance.show-invoice', compact('invoice'));
    }

    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|max:100',
            'payment_reference' => 'nullable|string|max:255'
        ]);

        $invoice->markAsPaid($validated['payment_method'], $validated['payment_reference']);

        return back()->with('success', 'Invoice marked as paid successfully.');
    }

    public function reports(Request $request)
    {
        $period = $request->get('period', 'monthly');
        
        // Revenue by period
        $revenueData = $this->getRevenueByPeriod($period);
        
        // Top departments by revenue
        $departmentRevenue = Invoice::join('departments', 'invoices.department_id', '=', 'departments.id')
                                   ->where('invoices.status', 'paid')
                                   ->selectRaw('departments.name, SUM(invoices.total_amount) as revenue')
                                   ->groupBy('departments.id', 'departments.name')
                                   ->orderByDesc('revenue')
                                   ->take(10)
                                   ->get();

        // Outstanding invoices by age
        $outstandingInvoices = [
            'current' => Invoice::whereIn('status', ['draft', 'sent'])
                               ->where('due_date', '>=', now())
                               ->sum('total_amount'),
            '30_days' => Invoice::whereIn('status', ['draft', 'sent'])
                               ->whereBetween('due_date', [now()->subDays(30), now()])
                               ->sum('total_amount'),
            '60_days' => Invoice::whereIn('status', ['draft', 'sent'])
                               ->whereBetween('due_date', [now()->subDays(60), now()->subDays(30)])
                               ->sum('total_amount'),
            'over_60' => Invoice::whereIn('status', ['draft', 'sent'])
                               ->where('due_date', '<', now()->subDays(60))
                               ->sum('total_amount')
        ];

        return view('finance.reports', compact('revenueData', 'departmentRevenue', 'outstandingInvoices', 'period'));
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Y') . '-';
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
                             ->orderBy('invoice_number', 'desc')
                             ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function getRevenueByPeriod($period)
    {
        $query = Invoice::where('status', 'paid');
        
        switch ($period) {
            case 'daily':
                return $query->whereDate('paid_at', '>=', now()->subDays(30))
                            ->selectRaw('DATE(paid_at) as period, SUM(total_amount) as revenue')
                            ->groupBy('period')
                            ->orderBy('period')
                            ->get();
            
            case 'weekly':
                return $query->whereDate('paid_at', '>=', now()->subWeeks(12))
                            ->selectRaw('YEARWEEK(paid_at) as period, SUM(total_amount) as revenue')
                            ->groupBy('period')
                            ->orderBy('period')
                            ->get();
            
            case 'yearly':
                return $query->selectRaw('YEAR(paid_at) as period, SUM(total_amount) as revenue')
                            ->groupBy('period')
                            ->orderBy('period')
                            ->get();
            
            default: // monthly
                return $query->whereYear('paid_at', now()->year)
                            ->selectRaw('MONTH(paid_at) as period, SUM(total_amount) as revenue')
                            ->groupBy('period')
                            ->orderBy('period')
                            ->get();
        }
    }
}

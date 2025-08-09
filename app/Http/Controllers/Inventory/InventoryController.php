
<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Council;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['council', 'department']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereRaw('current_stock <= minimum_stock');
                    break;
                case 'over':
                    $query->whereRaw('current_stock >= maximum_stock');
                    break;
                case 'normal':
                    $query->whereRaw('current_stock > minimum_stock AND current_stock < maximum_stock');
                    break;
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(20);

        $stats = [
            'total_items' => Item::count(),
            'total_value' => Item::sum('total_value'),
            'low_stock_items' => Item::whereRaw('current_stock <= minimum_stock')->count(),
            'expiring_soon' => Item::where('expiry_date', '<=', now()->addDays(30))
                                  ->where('expiry_date', '>', now())
                                  ->count()
        ];

        $categories = Item::distinct()->pluck('category')->filter();

        return view('inventory.index', compact('items', 'stats', 'categories'));
    }

    public function create()
    {
        $councils = Council::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $categories = Item::distinct()->pluck('category')->filter();
        
        return view('inventory.create', compact('councils', 'departments', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        $validated['item_code'] = $this->generateItemCode($validated['category']);
        $validated['total_value'] = $validated['current_stock'] * $validated['unit_cost'];
        $validated['status'] = Item::STATUS_ACTIVE;
        $validated['last_restock_date'] = now();

        $item = Item::create($validated);

        // Record initial stock movement
        $item->stockMovements()->create([
            'movement_type' => 'in',
            'quantity' => $validated['current_stock'],
            'previous_stock' => 0,
            'new_stock' => $validated['current_stock'],
            'reason' => 'Initial stock entry',
            'moved_by' => auth()->id()
        ]);

        return redirect()->route('inventory.index')
                        ->with('success', 'Inventory item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load(['council', 'department', 'stockMovements.user']);
        $recentMovements = $item->stockMovements()->latest()->take(20)->get();
        
        return view('inventory.show', compact('item', 'recentMovements'));
    }

    public function edit(Item $item)
    {
        $councils = Council::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $categories = Item::distinct()->pluck('category')->filter();
        
        return view('inventory.edit', compact('item', 'councils', 'departments', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'status' => 'required|in:active,inactive,discontinued',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        // Recalculate total value if unit cost changed
        if ($validated['unit_cost'] != $item->unit_cost) {
            $validated['total_value'] = $item->current_stock * $validated['unit_cost'];
        }

        $item->update($validated);

        return redirect()->route('inventory.index')
                        ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        
        return redirect()->route('inventory.index')
                        ->with('success', 'Inventory item deleted successfully.');
    }

    public function stockIn(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $item->updateStock($validated['quantity'], 'in', $validated['reason']);

        return back()->with('success', 'Stock added successfully.');
    }

    public function stockOut(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $item->current_stock,
            'reason' => 'required|string|max:255'
        ]);

        $item->updateStock($validated['quantity'], 'out', $validated['reason']);

        return back()->with('success', 'Stock removed successfully.');
    }

    public function lowStock()
    {
        $items = Item::whereRaw('current_stock <= minimum_stock')
                    ->with(['council', 'department'])
                    ->orderBy('current_stock', 'asc')
                    ->get();

        return view('inventory.low-stock', compact('items'));
    }

    public function expiringItems()
    {
        $items = Item::where('expiry_date', '<=', now()->addDays(30))
                    ->where('expiry_date', '>', now())
                    ->with(['council', 'department'])
                    ->orderBy('expiry_date', 'asc')
                    ->get();

        return view('inventory.expiring', compact('items'));
    }

    public function reports(Request $request)
    {
        $period = $request->get('period', 'monthly');
        
        // Stock value by category
        $categoryValue = Item::selectRaw('category, SUM(total_value) as value, COUNT(*) as count')
                            ->groupBy('category')
                            ->orderByDesc('value')
                            ->get();

        // Stock movement trends
        $movementTrends = $this->getMovementTrends($period);

        // Top items by value
        $topItems = Item::orderByDesc('total_value')->take(10)->get();

        return view('inventory.reports', compact('categoryValue', 'movementTrends', 'topItems', 'period'));
    }

    private function generateItemCode($category)
    {
        $prefix = strtoupper(substr($category, 0, 3)) . '-';
        $lastItem = Item::where('item_code', 'like', $prefix . '%')
                       ->orderBy('item_code', 'desc')
                       ->first();
        
        if ($lastItem) {
            $lastNumber = intval(substr($lastItem->item_code, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function getMovementTrends($period)
    {
        // This would implement stock movement trend analysis
        // For now, return empty array
        return [];
    }
}


<?php

namespace App\Http\Controllers\Housing;

use App\Http\Controllers\Controller;
use App\Models\Housing\Property;
use App\Models\Council;
use App\Models\Department;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['council', 'department', 'office', 'currentAllocation.tenant']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by property type
        if ($request->filled('type')) {
            $query->where('property_type', $request->type);
        }
        
        // Search by address or property code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('property_code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('suburb', 'like', "%{$search}%");
            });
        }
        
        $properties = $query->paginate(15);
        
        $stats = [
            'total' => Property::count(),
            'available' => Property::where('status', Property::STATUS_AVAILABLE)->count(),
            'occupied' => Property::where('status', Property::STATUS_OCCUPIED)->count(),
            'maintenance' => Property::where('status', Property::STATUS_MAINTENANCE)->count()
        ];
        
        return view('housing.properties.index', compact('properties', 'stats'));
    }

    public function create()
    {
        $councils = Council::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $offices = Office::where('is_active', true)->get();
        
        return view('housing.properties.create', compact('councils', 'departments', 'offices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'suburb' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'property_type' => 'required|in:house,flat,townhouse,room',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|integer|min:0|max:10',
            'size_sqm' => 'nullable|numeric|min:0',
            'rental_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'required|exists:departments,id',
            'office_id' => 'required|exists:offices,id'
        ]);
        
        // Generate unique property code
        $validated['property_code'] = $this->generatePropertyCode();
        
        Property::create($validated);
        
        return redirect()->route('housing.properties.index')
            ->with('success', 'Property created successfully.');
    }

    public function show(Property $property)
    {
        $property->load(['council', 'department', 'office', 'allocations.tenant', 'currentAllocation.tenant']);
        
        return view('housing.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $councils = Council::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $offices = Office::where('is_active', true)->get();
        
        return view('housing.properties.edit', compact('property', 'councils', 'departments', 'offices'));
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'suburb' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'property_type' => 'required|in:house,flat,townhouse,room',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|integer|min:0|max:10',
            'size_sqm' => 'nullable|numeric|min:0',
            'rental_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'required|exists:departments,id',
            'office_id' => 'required|exists:offices,id'
        ]);
        
        $property->update($validated);
        
        return redirect()->route('housing.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {
        // Check if property has active allocations
        if ($property->currentAllocation) {
            return back()->with('error', 'Cannot delete property with active allocation.');
        }
        
        $property->delete();
        
        return redirect()->route('housing.properties.index')
            ->with('success', 'Property deleted successfully.');
    }
    
    public function allocate(Request $request, Property $property)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);
        
        // Create allocation
        $property->allocations()->create([
            'tenant_id' => $validated['tenant_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'monthly_rent' => $validated['monthly_rent'],
            'deposit_paid' => $validated['deposit_paid'],
            'status' => 'active',
            'notes' => $validated['notes']
        ]);
        
        // Update property status
        $property->update(['status' => Property::STATUS_OCCUPIED]);
        
        return back()->with('success', 'Property allocated successfully.');
    }

    private function generatePropertyCode()
    {
        do {
            $code = 'PROP-' . strtoupper(Str::random(6));
        } while (Property::where('property_code', $code)->exists());
        
        return $code;
    }
}

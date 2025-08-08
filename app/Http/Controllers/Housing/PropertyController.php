<?php

namespace App\Http\Controllers\Housing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Housing\Property;
use App\Models\Office;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Property::with('office');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->property_type) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->bedrooms) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->suburb) {
            $query->where('suburb', 'like', '%' . $request->suburb . '%');
        }

        $properties = $query->orderBy('property_number')->paginate(15);

        $stats = [
            'total_properties' => Property::count(),
            'available' => Property::where('status', 'available')->count(),
            'occupied' => Property::where('status', 'occupied')->count(),
            'maintenance' => Property::where('status', 'under_maintenance')->count(),
        ];

        return view('housing.properties.index', compact('properties', 'stats'));
    }

    public function create()
    {
        $offices = Office::where('is_active', true)->get();
        return view('housing.properties.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_type' => 'required|in:house,apartment,townhouse,flat,studio',
            'address' => 'required|string',
            'suburb' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'size_sqm' => 'nullable|numeric|min:0',
            'rental_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'accessibility_features' => 'nullable|array',
            'office_id' => 'required|exists:offices,id',
            'gps_coordinates' => 'nullable|string',
            'property_condition' => 'required|in:excellent,good,fair,poor,condemned',
        ]);

        $property = Property::create([
            'property_number' => $this->generatePropertyNumber(),
            'property_type' => $request->property_type,
            'address' => $request->address,
            'suburb' => $request->suburb,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'size_sqm' => $request->size_sqm,
            'rental_amount' => $request->rental_amount,
            'description' => $request->description,
            'amenities' => $request->amenities ?? [],
            'accessibility_features' => $request->accessibility_features ?? [],
            'office_id' => $request->office_id,
            'gps_coordinates' => $request->gps_coordinates,
            'property_condition' => $request->property_condition,
            'next_inspection_due' => now()->addMonths(6),
        ]);

        return redirect()->route('housing.properties.show', $property)
                        ->with('success', 'Property created successfully.');
    }

    public function show(Property $property)
    {
        $property->load(['office', 'currentAllocation.tenant', 'allocations']);
        return view('housing.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $offices = Office::where('is_active', true)->get();
        return view('housing.properties.edit', compact('property', 'offices'));
    }

    public function update(Request $request, Property $property)
    {
        $request->validate([
            'property_type' => 'required|in:house,apartment,townhouse,flat,studio',
            'address' => 'required|string',
            'suburb' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'size_sqm' => 'nullable|numeric|min:0',
            'rental_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'accessibility_features' => 'nullable|array',
            'property_condition' => 'required|in:excellent,good,fair,poor,condemned',
            'status' => 'required|in:available,occupied,under_maintenance,under_renovation,condemned',
        ]);

        $property->update($request->only([
            'property_type', 'address', 'suburb', 'bedrooms', 'bathrooms',
            'size_sqm', 'rental_amount', 'description', 'amenities',
            'accessibility_features', 'property_condition', 'status'
        ]));

        return redirect()->route('housing.properties.show', $property)
                        ->with('success', 'Property updated successfully.');
    }

    public function updateStatus(Request $request, Property $property)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,under_maintenance,under_renovation,condemned',
            'notes' => 'nullable|string',
        ]);

        $property->update(['status' => $request->status]);

        return redirect()->route('housing.properties.show', $property)
                        ->with('success', 'Property status updated successfully.');
    }

    public function scheduleInspection(Request $request, Property $property)
    {
        $request->validate([
            'inspection_date' => 'required|date|after:today',
            'inspector_notes' => 'nullable|string',
        ]);

        $property->update([
            'next_inspection_due' => $request->inspection_date,
            'last_inspection_date' => now(),
        ]);

        return redirect()->route('housing.properties.show', $property)
                        ->with('success', 'Inspection scheduled successfully.');
    }

    public function available()
    {
        $properties = Property::where('status', 'available')
                             ->with('office')
                             ->orderBy('property_number')
                             ->paginate(15);

        return view('housing.properties.available', compact('properties'));
    }

    private function generatePropertyNumber()
    {
        $year = now()->year;
        $count = Property::whereYear('created_at', $year)->count() + 1;
        return "P{$year}" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Http\Controllers\Housing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Housing\Property;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::latest()->paginate(15);
        return view('housing.properties.index', compact('properties'));
    }

    public function create()
    {
        return view('housing.properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'type' => 'required|string',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'rent_amount' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
            'description' => 'nullable|string'
        ]);

        Property::create($validated);

        return redirect()->route('housing.properties.index')
            ->with('success', 'Property created successfully.');
    }

    public function show(Property $property)
    {
        return view('housing.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        return view('housing.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'type' => 'required|string',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'rent_amount' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
            'description' => 'nullable|string'
        ]);

        $property->update($validated);

        return redirect()->route('housing.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('housing.properties.index')
            ->with('success', 'Property deleted successfully.');
    }
}
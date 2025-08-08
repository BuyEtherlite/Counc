<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\Council;
use App\Models\Department;

class OfficeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $offices = Office::with(['council', 'department'])
                        ->orderBy('name')
                        ->paginate(15);
                        
        return view('admin.offices.index', compact('offices'));
    }

    public function create()
    {
        $councils = Council::all();
        $departments = Department::where('is_active', true)->get();
        
        return view('admin.offices.create', compact('councils', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_info' => 'required|string',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        Office::create($request->all());

        return redirect()->route('admin.offices.index')
                        ->with('success', 'Office created successfully.');
    }

    public function show(Office $office)
    {
        $office->load(['council', 'department', 'users']);
        return view('admin.offices.show', compact('office'));
    }

    public function edit(Office $office)
    {
        $councils = Council::all();
        $departments = Department::where('is_active', true)->get();
        
        return view('admin.offices.edit', compact('office', 'councils', 'departments'));
    }

    public function update(Request $request, Office $office)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_info' => 'required|string',
            'council_id' => 'required|exists:councils,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $office->update($request->all());

        return redirect()->route('admin.offices.index')
                        ->with('success', 'Office updated successfully.');
    }

    public function destroy(Office $office)
    {
        if ($office->users()->count() > 0) {
            return redirect()->route('admin.offices.index')
                            ->with('error', 'Cannot delete office with active users.');
        }

        $office->delete();

        return redirect()->route('admin.offices.index')
                        ->with('success', 'Office deleted successfully.');
    }
}
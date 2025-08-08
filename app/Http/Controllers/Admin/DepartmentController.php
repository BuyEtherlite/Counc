<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Council;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $departments = Department::with('council')
                                ->orderBy('name')
                                ->paginate(15);
                                
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $councils = Council::all();
        $availableModules = $this->getAvailableModules();
        
        return view('admin.departments.create', compact('councils', 'availableModules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'council_id' => 'required|exists:councils,id',
            'modules_access' => 'nullable|array',
        ]);

        Department::create($request->all());

        return redirect()->route('admin.departments.index')
                        ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load(['council', 'users', 'offices']);
        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $councils = Council::all();
        $availableModules = $this->getAvailableModules();
        
        return view('admin.departments.edit', compact('department', 'councils', 'availableModules'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'council_id' => 'required|exists:councils,id',
            'modules_access' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $department->update($request->all());

        return redirect()->route('admin.departments.index')
                        ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return redirect()->route('admin.departments.index')
                            ->with('error', 'Cannot delete department with active users.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
                        ->with('success', 'Department deleted successfully.');
    }

    private function getAvailableModules()
    {
        return [
            'housing' => 'Housing Management',
            'administration' => 'Administration CRM',
            'facilities' => 'Facility Bookings',
            'cemeteries' => 'Cemeteries & Grave Register',
            'property' => 'Property Management',
            'planning' => 'Town Planning',
            'water' => 'Water Management',
            'finance' => 'Finance',
            'inventory' => 'Inventory Management',
            'committee' => 'Committee Administration',
            'health' => 'Health Management',
            'emergency' => 'Emergency Services',
            'quality' => 'Quality Assurance',
            'security' => 'Access Control & Security',
            'audit' => 'Audit Trail',
        ];
    }
}
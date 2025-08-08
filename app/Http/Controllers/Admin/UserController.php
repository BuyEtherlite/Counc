<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Office;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin'); // Custom middleware for admin access
    }

    public function index()
    {
        $users = User::with(['department', 'office'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
                    
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $offices = Office::where('is_active', true)->get();
        
        return view('admin.users.create', compact('departments', 'offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,manager,user',
            'department_id' => 'nullable|exists:departments,id',
            'office_id' => 'nullable|exists:offices,id',
            'permissions' => 'nullable|array',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department_id' => $request->department_id,
            'office_id' => $request->office_id,
            'permissions' => $request->permissions ?? [],
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['department', 'office']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $departments = Department::where('is_active', true)->get();
        $offices = Office::where('is_active', true)->get();
        
        return view('admin.users.edit', compact('user', 'departments', 'offices'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,manager,user',
            'department_id' => 'nullable|exists:departments,id',
            'office_id' => 'nullable|exists:offices,id',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id,
            'office_id' => $request->office_id,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deletion of super admin users and self-deletion
        if ($user->role === 'super_admin' || $user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'Cannot delete this user.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        // Prevent deactivating super admin users and self
        if ($user->role === 'super_admin' || $user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'Cannot change status of this user.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.users.index')
                        ->with('success', "User {$status} successfully.");
    }
}
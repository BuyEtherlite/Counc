<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Department;
use App\Models\Office;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get dashboard statistics
        $stats = [
            'total_users' => User::where('is_active', true)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'total_offices' => Office::where('is_active', true)->count(),
            'recent_activities' => [], // TODO: Implement activity logging
        ];

        // Get user's accessible modules based on role and department
        $accessibleModules = $this->getUserAccessibleModules($user);

        return view('dashboard.index', compact('stats', 'accessibleModules', 'user'));
    }

    private function getUserAccessibleModules($user)
    {
        $allModules = [
            'housing' => [
                'name' => 'Housing Management',
                'icon' => '🏠',
                'items' => ['Waiting List', 'Allocations', 'Properties']
            ],
            'administration' => [
                'name' => 'Administration CRM',
                'icon' => '💼',
                'items' => ['Customer Services', 'Service Requests', 'Communications']
            ],
            'facilities' => [
                'name' => 'Facility Bookings',
                'icon' => '🏊‍♂️',
                'items' => ['Pool Bookings', 'Hall Rentals', 'Sports Facilities']
            ],
            'cemeteries' => [
                'name' => 'Cemeteries',
                'icon' => '⚰️',
                'items' => ['Grave Register', 'Burials', 'Maintenance']
            ],
            'property' => [
                'name' => 'Property Management',
                'icon' => '🏘️',
                'items' => ['Valuations', 'Leases', 'Land Records']
            ],
            'planning' => [
                'name' => 'Town Planning',
                'icon' => '🏗️',
                'items' => ['Applications', 'Approvals', 'Zoning']
            ],
            'water' => [
                'name' => 'Water Management',
                'icon' => '💧',
                'items' => ['Connections', 'Metering', 'Billing']
            ],
            'finance' => [
                'name' => 'Finance',
                'icon' => '💰',
                'items' => ['General Ledger', 'Billing', 'Receipts']
            ],
            'inventory' => [
                'name' => 'Inventory',
                'icon' => '📦',
                'items' => ['Stock Management', 'Procurement', 'Asset Register']
            ],
            'committee' => [
                'name' => 'Committee Administration',
                'icon' => '👥',
                'items' => ['Meetings', 'Agendas', 'Minutes']
            ],
        ];

        // Super admin gets access to all modules
        if ($user->role === 'super_admin') {
            return $allModules;
        }

        // Filter modules based on department permissions
        $accessibleModules = [];
        if ($user->department && $user->department->modules_access) {
            foreach ($user->department->modules_access as $moduleKey) {
                if (isset($allModules[$moduleKey])) {
                    $accessibleModules[$moduleKey] = $allModules[$moduleKey];
                }
            }
        }

        return $accessibleModules;
    }
}
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Office;

class DashboardApiController extends BaseApiController
{
    public function stats()
    {
        $stats = [
            'total_users' => User::where('is_active', true)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'total_offices' => Office::where('is_active', true)->count(),
            'active_sessions' => 0, // TODO: Implement session tracking
        ];

        return $this->successResponse($stats, 'Dashboard statistics retrieved successfully');
    }

    public function userModules(Request $request)
    {
        $user = $request->user();
        
        $allModules = [
            'housing' => [
                'name' => 'Housing Management',
                'icon' => '🏠',
                'description' => 'Manage waiting lists, allocations, and housing records',
                'features' => ['Waiting List', 'Allocations', 'Properties']
            ],
            'administration' => [
                'name' => 'Administration CRM',
                'icon' => '💼',
                'description' => 'Customer relationship management and service delivery',
                'features' => ['Customer Services', 'Service Requests', 'Communications']
            ],
            'facilities' => [
                'name' => 'Facility Bookings',
                'icon' => '🏊‍♂️',
                'description' => 'Manage bookings for pools, halls, and recreational facilities',
                'features' => ['Pool Bookings', 'Hall Rentals', 'Sports Facilities']
            ],
            'finance' => [
                'name' => 'Finance Management',
                'icon' => '💰',
                'description' => 'Financial management with QuickBooks compatibility',
                'features' => ['General Ledger', 'Billing', 'Receipts', 'Accounting Integration']
            ],
            'planning' => [
                'name' => 'Town Planning',
                'icon' => '🏗️',
                'description' => 'Development applications and planning approvals',
                'features' => ['Applications', 'Approvals', 'Zoning', 'Architectural Services']
            ],
            'water' => [
                'name' => 'Water Management',
                'icon' => '💧',
                'description' => 'Water connections, metering, and utility management',
                'features' => ['Connections', 'Metering', 'Billing', 'Quality Monitoring']
            ],
        ];

        // Super admin gets access to all modules
        if ($user->role === 'super_admin') {
            $accessibleModules = $allModules;
        } else {
            // Filter modules based on department permissions
            $accessibleModules = [];
            if ($user->department && $user->department->modules_access) {
                foreach ($user->department->modules_access as $moduleKey) {
                    if (isset($allModules[$moduleKey])) {
                        $accessibleModules[$moduleKey] = $allModules[$moduleKey];
                    }
                }
            }
        }

        return $this->successResponse($accessibleModules, 'User modules retrieved successfully');
    }
}
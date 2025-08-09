
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Council;
use App\Models\Department;
use App\Models\Office;
use App\Models\Housing\Property;
use App\Models\User;
use App\Models\Inventory\Item;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a council
        $council = Council::create([
            'name' => 'City Council',
            'code' => 'CC001',
            'description' => 'Main city council',
            'address' => '123 Main Street, City Center',
            'phone' => '+1-555-0123',
            'email' => 'info@citycouncil.gov',
            'website' => 'https://www.citycouncil.gov',
            'is_active' => true,
        ]);

        // Create a department
        $department = Department::create([
            'name' => 'Housing Department',
            'code' => 'HD001',
            'description' => 'Manages public housing',
            'council_id' => $council->id,
            'is_active' => true,
            'modules_access' => ['housing', 'finance', 'inventory']
        ]);

        // Create an office
        $office = Office::create([
            'name' => 'Main Office',
            'code' => 'MO001',
            'description' => 'Main housing office',
            'department_id' => $department->id,
            'location' => 'Downtown',
            'is_active' => true,
        ]);

        // Create a user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@citycouncil.gov',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $department->id,
            'office_id' => $office->id,
            'is_active' => true,
        ]);

        // Create sample properties
        Property::create([
            'property_code' => 'P001',
            'address' => '456 Oak Street',
            'suburb' => 'Downtown',
            'city' => 'Main City',
            'postal_code' => '12345',
            'property_type' => 'house',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'size_sqm' => 120.5,
            'rental_amount' => 1200.00,
            'deposit_amount' => 2400.00,
            'status' => 'available',
            'description' => 'Nice family house',
            'council_id' => $council->id,
            'department_id' => $department->id,
            'office_id' => $office->id,
            'current_stock' => 1,
            'minimum_stock' => 0,
        ]);

        // Create sample inventory items
        Item::create([
            'name' => 'Office Supplies',
            'description' => 'Basic office supplies',
            'sku' => 'OS001',
            'category' => 'Office',
            'current_stock' => 50,
            'minimum_stock' => 10,
            'unit_price' => 15.99,
            'supplier' => 'Office Supply Co',
            'location' => 'Storage Room A',
            'status' => 'active',
        ]);

        Item::create([
            'name' => 'Maintenance Tools',
            'description' => 'Basic maintenance tools',
            'sku' => 'MT001',
            'category' => 'Maintenance',
            'current_stock' => 5,
            'minimum_stock' => 15, // This will trigger low stock alert
            'unit_price' => 89.99,
            'supplier' => 'Tools & More',
            'location' => 'Storage Room B',
            'status' => 'active',
        ]);
    }
}

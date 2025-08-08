<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Council;
use App\Models\Office;
use App\Models\Housing\HousingApplication;
use App\Models\Housing\Property;
use App\Models\Housing\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HousingManagementTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $office;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $council = Council::create([
            'name' => 'Test Council',
            'address' => 'Test Address',
            'contact_info' => 'Test Contact',
            'is_primary' => true,
        ]);

        $this->office = Office::create([
            'name' => 'Test Office',
            'address' => 'Test Office Address',
            'contact_info' => 'Test Office Contact',
            'council_id' => $council->id,
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function user_can_create_housing_application()
    {
        $this->actingAs($this->user);

        $applicationData = [
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'applicant_phone' => '123-456-7890',
            'applicant_address' => '123 Test Street',
            'applicant_id_number' => 'ID123456789',
            'family_size' => 4,
            'monthly_income' => 2500.00,
            'employment_status' => 'Employed',
            'preferred_area' => 'Downtown',
            'housing_type_preference' => 'house',
            'special_needs' => 'Wheelchair accessible',
            'office_id' => $this->office->id,
        ];

        $response = $this->post(route('housing.applications.store'), $applicationData);

        $this->assertDatabaseHas('housing_applications', [
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'applicant_id_number' => 'ID123456789',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function housing_application_can_be_assessed()
    {
        $this->actingAs($this->user);

        $application = HousingApplication::create([
            'application_number' => 'HA202400001',
            'applicant_name' => 'Jane Doe',
            'applicant_email' => 'jane@example.com',
            'applicant_phone' => '123-456-7890',
            'applicant_address' => '456 Test Avenue',
            'applicant_id_number' => 'ID987654321',
            'family_size' => 2,
            'monthly_income' => 1800.00,
            'employment_status' => 'Employed',
            'application_date' => now(),
            'office_id' => $this->office->id,
        ]);

        $assessmentData = [
            'status' => 'on_waiting_list',
            'assessment_notes' => 'Approved for waiting list placement',
        ];

        $response = $this->post(route('housing.applications.assess', $application), $assessmentData);

        $this->assertDatabaseHas('housing_applications', [
            'id' => $application->id,
            'status' => 'on_waiting_list',
            'assessed_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('housing_waiting_list', [
            'housing_application_id' => $application->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function property_can_be_created()
    {
        $this->actingAs($this->user);

        $propertyData = [
            'property_type' => 'house',
            'address' => '789 Property Street',
            'suburb' => 'Test Suburb',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'size_sqm' => 120.5,
            'rental_amount' => 800.00,
            'description' => 'Nice family home',
            'amenities' => ['garden', 'parking'],
            'accessibility_features' => ['ramp'],
            'office_id' => $this->office->id,
            'property_condition' => 'good',
        ];

        $response = $this->post(route('housing.properties.store'), $propertyData);

        $this->assertDatabaseHas('properties', [
            'address' => '789 Property Street',
            'suburb' => 'Test Suburb',
            'bedrooms' => 3,
            'rental_amount' => 800.00,
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function waiting_list_positions_are_calculated_correctly()
    {
        // Create applications with different priority scores
        $application1 = HousingApplication::create([
            'application_number' => 'HA202400001',
            'applicant_name' => 'High Priority',
            'applicant_email' => 'high@example.com',
            'applicant_phone' => '123-456-7890',
            'applicant_address' => '123 Street',
            'applicant_id_number' => 'ID001',
            'family_size' => 5,
            'monthly_income' => 1500.00,
            'employment_status' => 'Employed',
            'application_date' => now()->subDays(30),
            'office_id' => $this->office->id,
        ]);

        $application2 = HousingApplication::create([
            'application_number' => 'HA202400002',
            'applicant_name' => 'Low Priority',
            'applicant_email' => 'low@example.com',
            'applicant_phone' => '123-456-7890',
            'applicant_address' => '456 Street',
            'applicant_id_number' => 'ID002',
            'family_size' => 1,
            'monthly_income' => 3500.00,
            'employment_status' => 'Employed',
            'application_date' => now()->subDays(10),
            'office_id' => $this->office->id,
        ]);

        // Add to waiting list
        $entry1 = WaitingList::create([
            'housing_application_id' => $application1->id,
            'priority_score' => 90,
            'date_added' => now()->subDays(30),
            'status' => 'active',
        ]);

        $entry2 = WaitingList::create([
            'housing_application_id' => $application2->id,
            'priority_score' => 30,
            'date_added' => now()->subDays(10),
            'status' => 'active',
        ]);

        // Recalculate positions
        WaitingList::recalculateAllPositions();

        $entry1->refresh();
        $entry2->refresh();

        // Higher priority should be position 1
        $this->assertEquals(1, $entry1->position);
        $this->assertEquals(2, $entry2->position);
    }

    /** @test */
    public function priority_score_is_calculated_correctly()
    {
        $application = new HousingApplication([
            'family_size' => 4,
            'monthly_income' => 1800.00,
            'special_needs' => 'Wheelchair accessible',
            'application_date' => now()->subMonths(6),
        ]);

        $score = $application->calculatePriorityScore();

        // Family size (4 * 10 = 40) + Low income (20) + Special needs (20) + Time waiting (6 * 2 = 12) = 92
        $this->assertGreaterThan(80, $score);
        $this->assertLessThan(100, $score);
    }
}
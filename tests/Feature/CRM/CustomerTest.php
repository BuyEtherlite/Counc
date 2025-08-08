<?php

namespace Tests\Feature\CRM;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\CRM\Customer;
use App\Models\User;

class CustomerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that customers can be created
     */
    public function test_customer_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $customerData = [
            'customer_number' => 'CUST001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'customer_type' => 'individual',
            'status' => 'active',
        ];

        $response = $this->actingAs($user)
            ->post(route('crm.customers.store'), $customerData);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'customer_number' => 'CUST001',
            'email' => 'john.doe@example.com',
        ]);
    }

    /**
     * Test customer listing
     */
    public function test_customers_can_be_listed(): void
    {
        $user = User::factory()->create();
        Customer::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('crm.customers.index'));

        $response->assertStatus(200);
    }
}

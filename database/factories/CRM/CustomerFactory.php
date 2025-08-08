<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CRM\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_number' => $this->faker->unique()->bothify('CUST###'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'id_number' => $this->faker->unique()->numerify('##########'),
            'customer_type' => $this->faker->randomElement(['individual', 'business', 'organization']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'registered_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }

    /**
     * Indicate that the customer is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the customer is a business.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_type' => 'business',
        ]);
    }
}

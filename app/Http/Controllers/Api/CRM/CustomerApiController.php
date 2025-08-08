<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\CRM\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerApiController extends BaseApiController
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->search, function ($query, $search) {
                return $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('customer_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate($request->per_page ?? 15);

        return $this->success('Customers retrieved successfully', $customers);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_number' => 'required|string|unique:customers',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'id_number' => 'nullable|string|unique:customers',
            'customer_type' => 'required|in:individual,business,organization',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $customer = Customer::create($validated);

        return $this->success('Customer created successfully', $customer, 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['contacts', 'interactions']);
        return $this->success('Customer retrieved successfully', $customer);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'customer_number' => 'sometimes|required|string|unique:customers,customer_number,' . $customer->id,
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'id_number' => 'nullable|string|unique:customers,id_number,' . $customer->id,
            'customer_type' => 'sometimes|required|in:individual,business,organization',
            'status' => 'sometimes|required|in:active,inactive,suspended',
        ]);

        $customer->update($validated);

        return $this->success('Customer updated successfully', $customer);
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return $this->success('Customer deleted successfully');
    }
}

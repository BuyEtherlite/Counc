<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('property_number')->unique();
            $table->enum('property_type', ['house', 'apartment', 'townhouse', 'flat', 'studio']);
            $table->text('address');
            $table->string('suburb');
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->decimal('size_sqm', 8, 2)->nullable();
            $table->decimal('rental_amount', 10, 2);
            $table->enum('status', ['available', 'occupied', 'under_maintenance', 'under_renovation', 'condemned'])->default('available');
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->json('accessibility_features')->nullable();
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            $table->string('gps_coordinates')->nullable();
            $table->enum('property_condition', ['excellent', 'good', 'fair', 'poor', 'condemned'])->default('good');
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_due')->nullable();
            $table->timestamps();

            $table->index(['status', 'property_type']);
            $table->index('suburb');
            $table->index('bedrooms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
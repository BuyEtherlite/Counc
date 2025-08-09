<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('housing_properties', function (Blueprint $table) {
            $table->id();
            $table->string('property_code')->unique();
            $table->text('address');
            $table->string('suburb')->nullable();
            $table->string('city');
            $table->string('postal_code')->nullable();
            $table->enum('property_type', ['house', 'flat', 'townhouse', 'room'])->default('house');
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('size_sqm', 10, 2)->nullable();
            $table->decimal('rental_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->json('coordinates')->nullable();
            $table->text('maintenance_notes')->nullable();
            
            // Inventory tracking columns
            $table->integer('current_stock')->default(1);
            $table->integer('minimum_stock')->default(0);
            $table->integer('maximum_stock')->default(1);
            
            // Foreign keys
            $table->unsignedBigInteger('council_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            
            $table->foreign('council_id')->references('id')->on('councils')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'property_type']);
            $table->index(['council_id', 'department_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('housing_properties');
    }
};

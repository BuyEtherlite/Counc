<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('facility_type_id')->constrained();
            $table->string('location');
            $table->integer('capacity')->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->json('amenities')->nullable();
            $table->string('status')->default('active'); // active, inactive, maintenance
            $table->boolean('bookable')->default(true);
            $table->json('operating_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};

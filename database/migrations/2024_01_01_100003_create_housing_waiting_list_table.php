<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housing_waiting_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_application_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->integer('priority_score');
            $table->date('date_added');
            $table->json('preferred_areas')->nullable();
            $table->string('housing_type_preference')->nullable();
            $table->text('special_requirements')->nullable();
            $table->enum('status', ['active', 'contacted', 'offered', 'declined', 'allocated', 'removed'])->default('active');
            $table->date('last_contacted')->nullable();
            $table->integer('contact_attempts')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'position']);
            $table->index('priority_score');
            $table->index('date_added');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_waiting_list');
    }
};
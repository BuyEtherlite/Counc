<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housing_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->text('applicant_address');
            $table->string('applicant_id_number');
            $table->integer('family_size')->default(1);
            $table->decimal('monthly_income', 10, 2);
            $table->string('employment_status');
            $table->string('preferred_area')->nullable();
            $table->string('housing_type_preference')->nullable();
            $table->text('special_needs')->nullable();
            $table->date('application_date');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'on_waiting_list'])->default('pending');
            $table->integer('priority_score')->default(0);
            $table->text('assessment_notes')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('office_id')->nullable()->constrained()->onDelete('set null');
            $table->json('documents')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority_score']);
            $table->index('application_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_applications');
    }
};
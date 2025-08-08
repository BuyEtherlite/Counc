<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housing_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('waiting_list_id')->nullable()->constrained('housing_waiting_list')->onDelete('set null');
            $table->foreignId('allocated_by')->constrained('users')->onDelete('cascade');
            $table->date('allocation_date');
            $table->date('move_in_date')->nullable();
            $table->date('lease_start_date');
            $table->date('lease_end_date')->nullable();
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->enum('status', ['offered', 'accepted', 'declined', 'active', 'terminated', 'expired'])->default('offered');
            $table->json('terms_conditions')->nullable();
            $table->json('special_conditions')->nullable();
            $table->text('allocation_notes')->nullable();
            $table->date('tenant_accepted_date')->nullable();
            $table->date('keys_handed_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'allocation_date']);
            $table->index('lease_start_date');
            $table->index('lease_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_allocations');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_id')->constrained('housing_allocations')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('id_number');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'evicted'])->default('active');
            $table->decimal('rent_balance', 10, 2)->default(0);
            $table->decimal('deposit_balance', 10, 2)->default(0);
            $table->integer('payment_day')->nullable();
            $table->string('termination_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'move_in_date']);
            $table->index('payment_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
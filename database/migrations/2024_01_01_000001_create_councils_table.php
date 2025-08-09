<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('councils', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('code', 191)->unique();
            $table->text('description')->nullable();
            $table->string('address', 191);
            $table->string('phone', 191);
            $table->string('email', 191);
            $table->string('website', 191)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('councils');
    }
};
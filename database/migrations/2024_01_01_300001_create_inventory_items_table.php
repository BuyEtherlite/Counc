
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('category');
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->string('supplier')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('housing_properties', function (Blueprint $table) {
            $table->string('property_code')->unique()->after('id');
            $table->string('suburb', 100)->after('address');
            $table->string('city', 100)->after('suburb');
            $table->string('postal_code', 20)->after('city');
            $table->enum('property_type', ['house', 'flat', 'townhouse', 'room'])->after('postal_code');
            $table->integer('bedrooms')->after('property_type');
            $table->integer('bathrooms')->after('bedrooms');
            $table->decimal('size_sqm', 8, 2)->nullable()->after('bathrooms');
            $table->decimal('rental_amount', 10, 2)->after('size_sqm');
            $table->decimal('deposit_amount', 10, 2)->after('rental_amount');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available')->after('deposit_amount');
            $table->text('description')->nullable()->after('status');
            $table->json('amenities')->nullable()->after('description');
            $table->json('coordinates')->nullable()->after('amenities');
            $table->text('maintenance_notes')->nullable()->after('coordinates');
            $table->foreignId('council_id')->constrained()->onDelete('cascade')->after('maintenance_notes');
            $table->foreignId('department_id')->constrained()->onDelete('cascade')->after('council_id');
            $table->foreignId('office_id')->constrained()->onDelete('cascade')->after('department_id');
            $table->softDeletes();
            
            // Remove old columns if they exist
            $table->dropColumn(['name', 'type']);
        });
    }

    public function down()
    {
        Schema::table('housing_properties', function (Blueprint $table) {
            $table->dropColumn([
                'property_code', 'suburb', 'city', 'postal_code', 'property_type',
                'bedrooms', 'bathrooms', 'size_sqm', 'rental_amount', 'deposit_amount',
                'status', 'description', 'amenities', 'coordinates', 'maintenance_notes',
                'council_id', 'department_id', 'office_id'
            ]);
            $table->dropSoftDeletes();
        });
    }
};

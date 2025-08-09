
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing foreign keys to housing_properties if they don't exist
        if (Schema::hasTable('housing_properties')) {
            Schema::table('housing_properties', function (Blueprint $table) {
                // Check if foreign keys don't exist before adding them
                if (!$this->foreignKeyExists('housing_properties', 'housing_properties_council_id_foreign')) {
                    $table->foreign('council_id')->references('id')->on('councils')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('housing_properties', 'housing_properties_department_id_foreign')) {
                    $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('housing_properties', 'housing_properties_office_id_foreign')) {
                    $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
                }
            });
        }

        // Add indexes for better performance
        if (Schema::hasTable('housing_applications')) {
            Schema::table('housing_applications', function (Blueprint $table) {
                if (!$this->indexExists('housing_applications', 'housing_applications_status_priority_score_index')) {
                    $table->index(['status', 'priority_score']);
                }
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!$this->indexExists('invoices', 'invoices_status_due_date_index')) {
                    $table->index(['status', 'due_date']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('housing_properties')) {
            Schema::table('housing_properties', function (Blueprint $table) {
                $table->dropForeign(['council_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['office_id']);
            });
        }

        if (Schema::hasTable('housing_applications')) {
            Schema::table('housing_applications', function (Blueprint $table) {
                $table->dropIndex(['status', 'priority_score']);
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['status', 'due_date']);
            });
        }
    }

    private function foreignKeyExists($table, $foreignKey)
    {
        $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);
        
        foreach ($foreignKeys as $key) {
            if ($key->getName() === $foreignKey) {
                return true;
            }
        }
        return false;
    }

    private function indexExists($table, $index)
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return array_key_exists($index, $indexes);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('contractors')) {
            Schema::table('contractors', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('contractors'));
                if (!in_array('contractors_status_index', $indexes)) { $table->index('status'); }
                if (!in_array('contractors_company_id_index', $indexes)) { $table->index('company_id'); }
                if (!in_array('contractors_created_at_index', $indexes)) { $table->index('created_at'); }
            });
        }
        if (Schema::hasTable('contractor_tags')) {
            Schema::table('contractor_tags', function (Blueprint $table) {
                $table->index(['contractor_id','tag']);
            });
        }
        if (Schema::hasTable('contractor_notes')) {
            Schema::table('contractor_notes', function (Blueprint $table) {
                $table->index(['contractor_id','created_at']);
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('contractors')) {
            Schema::table('contractors', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['company_id']);
                $table->dropIndex(['created_at']);
            });
        }
    }
};

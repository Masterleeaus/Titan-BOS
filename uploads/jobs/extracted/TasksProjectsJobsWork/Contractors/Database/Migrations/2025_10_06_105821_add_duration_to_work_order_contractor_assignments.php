<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('work_order_contractor_assignments') && !Schema::hasColumn('work_order_contractor_assignments','duration_minutes')) {
            Schema::table('work_order_contractor_assignments', function (Blueprint $table) {
                $table->integer('duration_minutes')->nullable()->after('scheduled_at');
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('work_order_contractor_assignments') && Schema::hasColumn('work_order_contractor_assignments','duration_minutes')) {
            Schema::table('work_order_contractor_assignments', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }
};

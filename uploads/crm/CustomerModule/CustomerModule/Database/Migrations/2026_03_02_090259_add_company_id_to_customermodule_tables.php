<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('searched_data') && !Schema::hasColumn('searched_data', 'company_id')) {
            Schema::table('searched_data', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('subscribe_newsletters') && !Schema::hasColumn('subscribe_newsletters', 'company_id')) {
            Schema::table('subscribe_newsletters', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // intentionally non-destructive
    }
};

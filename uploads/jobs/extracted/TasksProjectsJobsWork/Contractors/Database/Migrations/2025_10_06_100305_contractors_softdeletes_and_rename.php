<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('subcontractors') && !Schema::hasTable('contractors')) {
            Schema::rename('subcontractors', 'contractors');
        }
        if (Schema::hasTable('contractors')) {
            Schema::table('contractors', function (Blueprint $table) {
                if (!in_array('deleted_at', Schema::getColumnListing('contractors'))) {
                    $table->softDeletes();
                }
            });
        } else {
            Schema::create('contractors', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('contractors') && in_array('deleted_at', Schema::getColumnListing('contractors'))) {
            Schema::table('contractors', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    if (Schema::hasTable('work_order_contractor_assignments')) {
      Schema::table('work_order_contractor_assignments', function (Blueprint $table) {
        if (!Schema::hasColumn('work_order_contractor_assignments','status')) {
          $table->string('status')->default('assigned')->after('role');
        }
        if (!Schema::hasColumn('work_order_contractor_assignments','accepted_at')) {
          $table->timestamp('accepted_at')->nullable()->after('status');
        }
        if (!Schema::hasColumn('work_order_contractor_assignments','en_route_at')) {
          $table->timestamp('en_route_at')->nullable()->after('accepted_at');
        }
        if (!Schema::hasColumn('work_order_contractor_assignments','started_at')) {
          $table->timestamp('started_at')->nullable()->after('en_route_at');
        }
        if (!Schema::hasColumn('work_order_contractor_assignments','completed_at')) {
          $table->timestamp('completed_at')->nullable()->after('started_at');
        }
      });
    }
  }
  public function down(): void {
    if (Schema::hasTable('work_order_contractor_assignments')) {
      Schema::table('work_order_contractor_assignments', function (Blueprint $table) {
        foreach (['status','accepted_at','en_route_at','started_at','completed_at'] as $c) {
          if (Schema::hasColumn('work_order_contractor_assignments',$c)) $table->dropColumn($c);
        }
      });
    }
  }
};
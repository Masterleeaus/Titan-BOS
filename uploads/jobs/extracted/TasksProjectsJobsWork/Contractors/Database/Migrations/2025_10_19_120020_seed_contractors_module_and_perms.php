<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('modules')) {
            DB::table('modules')->updateOrInsert(
                ['module_name' => 'Contractors'],
                ['module_status' => 1, 'module_description' => 'Contractors module', 'updated_at' => now(), 'created_at' => now()]
            );
        }
        if (Schema::hasTable('permissions')) {
            foreach (['contractors.view','contractors.create','contractors.update','contractors.delete','contractors.settings'] as $name) {
                DB::table('permissions')->updateOrInsert(['name' => $name], ['guard_name' => 'web', 'updated_at' => now(), 'created_at' => now()]);
            }
        }
    }
    public function down(): void {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('name',['contractors.view','contractors.create','contractors.update','contractors.delete','contractors.settings'])->delete();
        }
        if (Schema::hasTable('modules')) {
            DB::table('modules')->where('module_name','Contractors')->delete();
        }
    }
};

<?php

namespace Modules\Contractors\Database\Seeders;

use Illuminate\Database\Seeder;

class ContractorsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = array_keys(config('contractors.permissions_matrix', [])) ?: config('contractors.permissions', []);
        foreach ($perms as $p) {
            try { \DB::table('permissions')->insert(['name'=>$p, 'created_at'=>now(), 'updated_at'=>now()]); }
            catch (\Throwable $e) {}
        }
    }
}

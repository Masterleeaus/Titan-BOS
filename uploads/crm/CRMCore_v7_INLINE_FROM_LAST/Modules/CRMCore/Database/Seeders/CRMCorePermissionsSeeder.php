<?php

namespace Modules\CRMCore\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CRMCorePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permTable = Schema::hasTable('permissions') ? 'permissions' : null;
        if ($permTable) {
            $perms = ['crmcore.view','crmcore.create','crmcore.update','crmcore.delete','crmcore.admin'];
            foreach ($perms as $p) {
                $exists = DB::table($permTable)->where('name', $p)->exists();
                if (!$exists) {
                    DB::table($permTable)->insert(['name'=>$p,'guard_name'=>'web','created_at'=>now(),'updated_at'=>now()]);
                }
            }
        }
        $menu = Schema::hasTable('custom_menus') ? 'custom_menus' : (Schema::hasTable('menus') ? 'menus' : null);
        if ($menu) {
            $exists = DB::table($menu)->where('key', 'crmcore')->exists();
            if (!$exists) {
                DB::table($menu)->insert([
                    'key' => 'crmcore',
                    'title' => 'CRM Core',
                    'url' => '/crmcore',
                    'icon' => 'fa fa-users',
                    'sort_order' => 50,
                    'visible' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

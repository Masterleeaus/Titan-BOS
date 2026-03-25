<?php

namespace Modules\Contractors\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContractorDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('menus')) {
            $exists = DB::table('menus')->where('slug', 'contractors')->exists();
            if (!$exists) {
                DB::table('menus')->insert([
                    'title' => 'Contractors',
                    'slug'  => 'contractors',
                    'url'   => '/admin/contractors',
                    'icon'  => 'users',
                    'order' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

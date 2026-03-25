<?php

namespace Modules\Contractors\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContractorUninstall extends Command
{
    protected $signature = 'contractor:uninstall';
    protected $description = 'Remove menu entries created by the Contractor module';

    public function handle(): int
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->where('slug','contractors')->delete();
            $this->info('Removed menu entries.');
        } else {
            $this->info('No menus table present; nothing to remove.');
        }
        return self::SUCCESS;
    }
}

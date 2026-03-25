<?php

namespace Modules\CustomerModule\Console;

use Illuminate\Console\Command;

class ActivateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run: php artisan module:activate CustomerModule
     */
    protected $signature = 'module:activate {module_slug?}';

    /**
     * The console command description.
     */
    protected $description = 'Activate CustomerModule module (Worksuite-compatible stub).';

    public function handle(): int
    {
        $this->info('CustomerModule: activation stub complete.');
        return self::SUCCESS;
    }
}

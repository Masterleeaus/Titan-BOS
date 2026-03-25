<?php

namespace Modules\CRMCore\Http\Controllers\Api\V1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Bus;

class HealthController extends BaseController
{
    public function show()
    {
        $checks = [
            'config' => config('crmcore.permissions') !== null,
        ];

        try { DB::select('SELECT 1'); $checks['db'] = true; }
        catch (\Throwable $e) { $checks['db'] = false; $checks['db_error'] = $e->getMessage(); }

        try {
            $driver = Config::get('queue.default');
            Bus::dispatchSync(new class { public function __invoke() {} });
            $checks['queue'] = ['ok' => true, 'driver' => $driver];
        } catch (\Throwable $e) {
            $checks['queue'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        $ok = ($checks['config'] ?? false) && ($checks['db'] ?? false);
        return response()->json(['ok' => $ok, 'checks' => $checks]);
    }
}

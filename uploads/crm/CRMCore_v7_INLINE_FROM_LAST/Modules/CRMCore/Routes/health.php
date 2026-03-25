<?php

use Illuminate\Support\Facades\Route;
use Modules\CRMCore\Http\Controllers\Api\V1\HealthController;

Route::prefix('api/crmcore')->middleware(['api'])->group(function () {
    Route::get('/health', [HealthController::class, 'show']);
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Contractors\Http\Controllers\ContractorController;

Route::middleware(['api','auth:sanctum'])->prefix('contractors')->group(function () {
    $pView = config('contractor.permissions.abilities.view', 'contractor.view');
$pCreate = config('contractor.permissions.abilities.create', 'contractor.create');
$pUpdate = config('contractor.permissions.abilities.update', 'contractor.update');
$pDelete = config('contractor.permissions.abilities.delete', 'contractor.delete');

    Route::get('/', [ContractorController::class, 'index'])->name('sanctum.contractors.index');
    Route::post('/', [ContractorController::class, 'store'])->name('sanctum.contractors.store');
    Route::get('/{contractor}', [ContractorController::class, 'edit'])->name('sanctum.contractors.show');
    Route::put('/{contractor}', [ContractorController::class, 'update'])->name('sanctum.contractors.update');
    Route::delete('/{contractor}', [ContractorController::class, 'destroy'])->name('sanctum.contractors.destroy');
});

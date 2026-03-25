<?php

use Illuminate\Support\Facades\Route;
use Modules\Contractors\Http\Controllers\ContractorController;

Route::middleware(['api','auth'])->prefix('contractors')->group(function () {
    $pView = config('contractor.permissions.abilities.view', 'contractor.view');
$pCreate = config('contractor.permissions.abilities.create', 'contractor.create');
$pUpdate = config('contractor.permissions.abilities.update', 'contractor.update');
$pDelete = config('contractor.permissions.abilities.delete', 'contractor.delete');

    Route::get('/', [ContractorController::class, 'index'])->name('api.contractors.index')->middleware('can:'.$pView);
    Route::post('/', [ContractorController::class, 'store'])->name('api.contractors.store')->middleware('can:'.$pCreate);
    Route::get('/{contractor}', [ContractorController::class, 'edit'])->name('api.contractors.show')->middleware('can:'.$pView);
    Route::put('/{contractor}', [ContractorController::class, 'update'])->name('api.contractors.update')->middleware('can:'.$pUpdate);
    Route::delete('/{contractor}', [ContractorController::class, 'destroy'])->name('api.contractors.destroy')->middleware('can:'.$pDelete);
});


<?php
use Illuminate\Support\Facades\Route;
use Modules\Contractors\Http\Controllers\AssignmentController;

Route::middleware(['auth','can:contractors.view_assignments'])->prefix('contractors')->group(function () {
    Route::get('/assignments', [AssignmentController::class, 'index']);
});
Route::middleware(['auth','can:contractors.assign'])->prefix('contractors')->group(function () {
    Route::post('/assignments', [AssignmentController::class, 'store']);
    Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy']);
});

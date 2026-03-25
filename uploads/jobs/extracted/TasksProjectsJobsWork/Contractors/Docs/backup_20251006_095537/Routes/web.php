<?php

use Illuminate\Support\Facades\Route;
use Modules\Contractors\Http\Controllers\Admin\ContractorController;

Route::middleware(['web','auth'])->prefix('admin')->group(function() {
    Route::get('contractors', [ContractorController::class, 'index'])->name('admin.contractors.index');
    Route::get('contractors/create', [ContractorController::class, 'create'])->name('admin.contractors.create');
    Route::post('contractors', [ContractorController::class, 'store'])->name('admin.contractors.store');
    Route::get('contractors/{contractor}', [ContractorController::class, 'show'])->name('admin.contractors.show');
    Route::get('contractors/{contractor}/edit', [ContractorController::class, 'edit'])->name('admin.contractors.edit');
    Route::put('contractors/{contractor}', [ContractorController::class, 'update'])->name('admin.contractors.update');
    Route::delete('contractors/{contractor}', [ContractorController::class, 'destroy'])->name('admin.contractors.destroy');

    // AI assist endpoint
    Route::post('contractors/ai/suggest', [ContractorController::class, 'createWithAi'])->name('admin.contractors.ai.suggest');
});

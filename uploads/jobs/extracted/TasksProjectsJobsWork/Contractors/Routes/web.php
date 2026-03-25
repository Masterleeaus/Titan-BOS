<?php
use Modules\Contractors\Http\Controllers\MyDayController;
use Modules\Contractors\Http\Controllers\ScheduleController;

use Illuminate\Support\Facades\Route;
use Modules\Contractors\Http\Controllers\ContractorController;

Route::middleware(['web','auth'])->group(function () {
    Route::post('/contractor/bulk/status', [ContractorController::class, 'bulkUpdateStatus'])->name('contractor.bulk.status');
    Route::post('/contractor/bulk/delete', [ContractorController::class, 'bulkDelete'])->name('contractor.bulk.delete');
    Route::get('/contractor/export.xlsx', [ContractorController::class, 'exportXlsx'])->name('contractor.export.xlsx');
    Route::get('/contractor/logs', [ContractorController::class, 'logs'])->name('contractor.logs');
    Route::post('/contractor/{contractor}/tags', [ContractorController::class, 'addTag'])->name('contractor.tag.add');
    Route::post('/contractor/{contractor}/notes', [ContractorController::class, 'addNote'])->name('contractor.note.add');

    Route::get('/contractor/ping', [ContractorController::class, 'ping'])->name('contractor.ping');
    Route::get('/contractor/export.csv', [ContractorController::class, 'exportCsv'])->name('contractor.export');
    Route::get('/contractor', [ContractorController::class, 'index'])->name('contractor.index');
    Route::get('/contractor/create', [ContractorController::class, 'create'])->name('contractor.create');
    Route::post('/contractor', [ContractorController::class, 'store'])->name('contractor.store');
    Route::get('/contractor/{contractor}/edit', [ContractorController::class, 'edit'])->name('contractor.edit');
    Route::put('/contractor/{contractor}', [ContractorController::class, 'update'])->name('contractor.update');
    Route::delete('/contractor/{contractor}', [ContractorController::class, 'destroy'])->name('contractor.destroy');
});


<?php
use Modules\Contractors\Http\Controllers\MyDayController;
use Modules\Contractors\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;
use Modules\Contractors\Http\Controllers\AssignmentController;

Route::middleware(['web','can:contractors.view_assignments'])->group(function () {
    Route::get('/contractors/assignments', [AssignmentController::class, 'index'])->name('contractors.assignments.index');
});
Route::middleware(['web','can:contractors.assign'])->group(function () {
    Route::post('/contractors/assignments', [AssignmentController::class, 'store'])->name('contractors.assignments.store');
    Route::delete('/contractors/assignments/{id}', [AssignmentController::class, 'destroy'])->name('contractors.assignments.destroy');
});

Route::middleware(['web','can:contractors.view_assignments'])->group(function () {
    Route::get('/contractors/schedule', [ScheduleController::class, 'day'])->name('contractors.schedule.day');
});

Route::middleware(['web','auth'])->group(function () {
  Route::get('/contractors/my-day', [MyDayController::class, 'index'])->name('contractors.myday');
  Route::post('/contractors/my-day/{id}/status', [MyDayController::class, 'status'])->name('contractors.myday.status');
});

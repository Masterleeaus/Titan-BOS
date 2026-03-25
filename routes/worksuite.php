<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkSuite\Customers\CustomerController;
use App\Http\Controllers\WorkSuite\Customers\LeadController;
use App\Http\Controllers\WorkSuite\Customers\FollowUpController;
use App\Http\Controllers\WorkSuite\Jobs\JobController;
use App\Http\Controllers\WorkSuite\Jobs\MilestoneController;
use App\Http\Controllers\WorkSuite\Checklist\ChecklistController;
use App\Http\Controllers\WorkSuite\Schedule\ScheduleController;
use App\Http\Controllers\WorkSuite\Team\CleanerController;
use App\Http\Controllers\WorkSuite\Team\AttendanceController;
use App\Http\Controllers\WorkSuite\Team\LeaveController;
use App\Http\Controllers\WorkSuite\Team\ShiftController;
use App\Http\Controllers\WorkSuite\Team\DepartmentController;
use App\Http\Controllers\WorkSuite\Money\QuoteController;
use App\Http\Controllers\WorkSuite\Money\InvoiceController;
use App\Http\Controllers\WorkSuite\Money\PaymentController;
use App\Http\Controllers\WorkSuite\Money\ExpenseController;
use App\Http\Controllers\WorkSuite\Money\CreditController;
use App\Http\Controllers\WorkSuite\ServiceRequests\TicketController;
use App\Http\Controllers\WorkSuite\Playbooks\PlaybookController;
use App\Http\Controllers\WorkSuite\Insights\InsightController;
use App\Http\Controllers\WorkSuite\TeamChat\TeamChatController;

/*
|--------------------------------------------------------------------------
| WorkCore Cleaning BOS Routes
|--------------------------------------------------------------------------
| All routes follow the dashboard.user.* naming convention.
| Business language is resolved via VerticalLanguageResolver (default: cleaning).
|
| Conflict note:
|   dashboard.user.team.* is already used by the AI collaboration team feature
|   in panel.php. WorkCore HR/Staff routes are prefixed with 'bos-team' to avoid
|   overriding that existing feature. Document in docs/integration/conflicts-resolved.md.
*/

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard/user')
    ->name('dashboard.user.')
    ->group(function () {

        // ── Customers ─────────────────────────────────────────────────────
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        });

        // ── Leads ──────────────────────────────────────────────────────────
        Route::prefix('leads')->name('leads.')->group(function () {
            Route::get('/', [LeadController::class, 'index'])->name('index');
            Route::get('/create', [LeadController::class, 'create'])->name('create');
            Route::post('/', [LeadController::class, 'store'])->name('store');
            Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
            Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
            Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
            Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
            Route::post('/{lead}/convert', [LeadController::class, 'convert'])->name('convert');
        });

        // ── Follow-Ups ────────────────────────────────────────────────────
        Route::prefix('followups')->name('followups.')->group(function () {
            Route::get('/', [FollowUpController::class, 'index'])->name('index');
            Route::post('/', [FollowUpController::class, 'store'])->name('store');
            Route::put('/{followup}', [FollowUpController::class, 'update'])->name('update');
            Route::delete('/{followup}', [FollowUpController::class, 'destroy'])->name('destroy');
            Route::patch('/{followup}/complete', [FollowUpController::class, 'complete'])->name('complete');
        });

        // ── Jobs (Projects) ───────────────────────────────────────────────
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [JobController::class, 'index'])->name('index');
            Route::get('/create', [JobController::class, 'create'])->name('create');
            Route::post('/', [JobController::class, 'store'])->name('store');
            Route::get('/{job}', [JobController::class, 'show'])->name('show');
            Route::get('/{job}/edit', [JobController::class, 'edit'])->name('edit');
            Route::put('/{job}', [JobController::class, 'update'])->name('update');
            Route::delete('/{job}', [JobController::class, 'destroy'])->name('destroy');
            Route::post('/{job}/timer/start', [JobController::class, 'timerStart'])->name('timer.start');
            Route::post('/{job}/timer/stop', [JobController::class, 'timerStop'])->name('timer.stop');
            Route::resource('{job}/milestones', MilestoneController::class)->except(['index', 'show']);
        });

        // ── Cleaning Checklist (Tasks) ────────────────────────────────────
        Route::prefix('checklists')->name('checklists.')->group(function () {
            Route::get('/', [ChecklistController::class, 'index'])->name('index');
            Route::get('/create', [ChecklistController::class, 'create'])->name('create');
            Route::post('/', [ChecklistController::class, 'store'])->name('store');
            Route::get('/{checklist}', [ChecklistController::class, 'show'])->name('show');
            Route::put('/{checklist}', [ChecklistController::class, 'update'])->name('update');
            Route::delete('/{checklist}', [ChecklistController::class, 'destroy'])->name('destroy');
            Route::patch('/{checklist}/complete', [ChecklistController::class, 'complete'])->name('complete');
        });

        // ── Schedule & Dispatch (Calendar) ────────────────────────────────
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::get('/calendar', [ScheduleController::class, 'calendar'])->name('calendar');
            Route::post('/events', [ScheduleController::class, 'storeEvent'])->name('events.store');
            Route::put('/events/{event}', [ScheduleController::class, 'updateEvent'])->name('events.update');
            Route::delete('/events/{event}', [ScheduleController::class, 'destroyEvent'])->name('events.destroy');
            Route::get('/availability', [ScheduleController::class, 'availability'])->name('availability');
        });

        // ── BOS Team / HR ─────────────────────────────────────────────────
        // Prefixed 'bos-team' to avoid collision with AI team at dashboard.user.team.*
        Route::prefix('bos-team')->name('bos-team.')->group(function () {
            Route::get('/', [CleanerController::class, 'index'])->name('index');
            Route::get('/create', [CleanerController::class, 'create'])->name('create');
            Route::post('/', [CleanerController::class, 'store'])->name('store');
            Route::get('/{cleaner}', [CleanerController::class, 'show'])->name('show');
            Route::get('/{cleaner}/edit', [CleanerController::class, 'edit'])->name('edit');
            Route::put('/{cleaner}', [CleanerController::class, 'update'])->name('update');
            Route::delete('/{cleaner}', [CleanerController::class, 'destroy'])->name('destroy');

            Route::prefix('attendance')->name('attendance.')->group(function () {
                Route::get('/', [AttendanceController::class, 'index'])->name('index');
                Route::post('/', [AttendanceController::class, 'store'])->name('store');
                Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
                Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('leave')->name('leave.')->group(function () {
                Route::get('/', [LeaveController::class, 'index'])->name('index');
                Route::post('/', [LeaveController::class, 'store'])->name('store');
                Route::patch('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
                Route::patch('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
                Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('shifts')->name('shifts.')->group(function () {
                Route::get('/', [ShiftController::class, 'index'])->name('index');
                Route::post('/', [ShiftController::class, 'store'])->name('store');
                Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
                Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
            });

            Route::resource('departments', DepartmentController::class)->except(['show']);
        });

        // ── Money (Finance) ───────────────────────────────────────────────
        Route::prefix('money')->name('money.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::resource('quotes', QuoteController::class);
            Route::resource('invoices', InvoiceController::class);
            Route::patch('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
            Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
            Route::resource('payments', PaymentController::class)->only(['index', 'show', 'destroy']);
            Route::resource('expenses', ExpenseController::class);
            Route::resource('credits', CreditController::class)->only(['index', 'store', 'destroy']);
        });

        // ── Service Requests (Tickets) ────────────────────────────────────
        Route::prefix('service-requests')->name('service-requests.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::get('/create', [TicketController::class, 'create'])->name('create');
            Route::post('/', [TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
            Route::patch('/{ticket}/close', [TicketController::class, 'close'])->name('close');
            Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        });

        // ── Playbooks (Knowledge Base) ────────────────────────────────────
        Route::prefix('playbooks')->name('playbooks.')->group(function () {
            Route::get('/', [PlaybookController::class, 'index'])->name('index');
            Route::get('/create', [PlaybookController::class, 'create'])->name('create');
            Route::post('/', [PlaybookController::class, 'store'])->name('store');
            Route::get('/{playbook}', [PlaybookController::class, 'show'])->name('show');
            Route::get('/{playbook}/edit', [PlaybookController::class, 'edit'])->name('edit');
            Route::put('/{playbook}', [PlaybookController::class, 'update'])->name('update');
            Route::delete('/{playbook}', [PlaybookController::class, 'destroy'])->name('destroy');
        });

        // ── Insights (Reports) ────────────────────────────────────────────
        Route::prefix('insights')->name('insights.')->group(function () {
            Route::get('/', [InsightController::class, 'index'])->name('index');
            Route::get('/jobs', [InsightController::class, 'jobs'])->name('jobs');
            Route::get('/finance', [InsightController::class, 'finance'])->name('finance');
            Route::get('/team', [InsightController::class, 'team'])->name('team');
            Route::get('/customers', [InsightController::class, 'customers'])->name('customers');
        });

        // ── Team Chat (Internal Messaging only — not customer inbox) ──────
        Route::prefix('team-chat')->name('team-chat.')->group(function () {
            Route::get('/', [TeamChatController::class, 'index'])->name('index');
            Route::post('/messages', [TeamChatController::class, 'store'])->name('messages.store');
            Route::get('/messages/{channel}', [TeamChatController::class, 'messages'])->name('messages');
        });
    });

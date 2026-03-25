# WorkCore — Routes Added

All routes are defined in `routes/worksuite.php` and loaded by `RouteServiceProvider`
inside the `web + auth` middleware group.

## Route Index

| Route name | Method | URI | Controller |
|------------|--------|-----|------------|
| `dashboard.user.customers.index` | GET | `/dashboard/user/customers` | `WorkSuite\Customers\CustomerController@index` |
| `dashboard.user.customers.create` | GET | `/dashboard/user/customers/create` | `@create` |
| `dashboard.user.customers.store` | POST | `/dashboard/user/customers` | `@store` |
| `dashboard.user.customers.show` | GET | `/dashboard/user/customers/{customer}` | `@show` |
| `dashboard.user.customers.edit` | GET | `/dashboard/user/customers/{customer}/edit` | `@edit` |
| `dashboard.user.customers.update` | PUT | `/dashboard/user/customers/{customer}` | `@update` |
| `dashboard.user.customers.destroy` | DELETE | `/dashboard/user/customers/{customer}` | `@destroy` |
| `dashboard.user.leads.index` | GET | `/dashboard/user/leads` | `WorkSuite\Customers\LeadController@index` |
| `dashboard.user.leads.convert` | POST | `/dashboard/user/leads/{lead}/convert` | `@convert` |
| `dashboard.user.followups.index` | GET | `/dashboard/user/followups` | `WorkSuite\Customers\FollowUpController@index` |
| `dashboard.user.followups.complete` | PATCH | `/dashboard/user/followups/{followup}/complete` | `@complete` |
| `dashboard.user.jobs.index` | GET | `/dashboard/user/jobs` | `WorkSuite\Jobs\JobController@index` |
| `dashboard.user.jobs.timer.start` | POST | `/dashboard/user/jobs/{job}/timer/start` | `@timerStart` |
| `dashboard.user.jobs.timer.stop` | POST | `/dashboard/user/jobs/{job}/timer/stop` | `@timerStop` |
| `dashboard.user.checklists.index` | GET | `/dashboard/user/checklists` | `WorkSuite\Checklist\ChecklistController@index` |
| `dashboard.user.checklists.complete` | PATCH | `/dashboard/user/checklists/{checklist}/complete` | `@complete` |
| `dashboard.user.schedule.index` | GET | `/dashboard/user/schedule` | `WorkSuite\Schedule\ScheduleController@index` |
| `dashboard.user.schedule.calendar` | GET | `/dashboard/user/schedule/calendar` | `@calendar` |
| `dashboard.user.schedule.availability` | GET | `/dashboard/user/schedule/availability` | `@availability` |
| `dashboard.user.bos-team.index` | GET | `/dashboard/user/bos-team` | `WorkSuite\Team\CleanerController@index` |
| `dashboard.user.bos-team.attendance.index` | GET | `/dashboard/user/bos-team/attendance` | `WorkSuite\Team\AttendanceController@index` |
| `dashboard.user.bos-team.leave.index` | GET | `/dashboard/user/bos-team/leave` | `WorkSuite\Team\LeaveController@index` |
| `dashboard.user.bos-team.leave.approve` | PATCH | `/dashboard/user/bos-team/leave/{leave}/approve` | `@approve` |
| `dashboard.user.bos-team.leave.reject` | PATCH | `/dashboard/user/bos-team/leave/{leave}/reject` | `@reject` |
| `dashboard.user.bos-team.shifts.index` | GET | `/dashboard/user/bos-team/shifts` | `WorkSuite\Team\ShiftController@index` |
| `dashboard.user.bos-team.departments.*` | resource | `/dashboard/user/bos-team/departments` | `WorkSuite\Team\DepartmentController` |
| `dashboard.user.money.index` | GET | `/dashboard/user/money` | `WorkSuite\Money\InvoiceController@index` |
| `dashboard.user.money.quotes.*` | resource | `/dashboard/user/money/quotes` | `WorkSuite\Money\QuoteController` |
| `dashboard.user.money.invoices.*` | resource | `/dashboard/user/money/invoices` | `WorkSuite\Money\InvoiceController` |
| `dashboard.user.money.invoices.mark-paid` | PATCH | `/dashboard/user/money/invoices/{invoice}/mark-paid` | `@markPaid` |
| `dashboard.user.money.invoices.send` | POST | `/dashboard/user/money/invoices/{invoice}/send` | `@send` |
| `dashboard.user.money.payments.*` | resource | `/dashboard/user/money/payments` | `WorkSuite\Money\PaymentController` |
| `dashboard.user.money.expenses.*` | resource | `/dashboard/user/money/expenses` | `WorkSuite\Money\ExpenseController` |
| `dashboard.user.money.credits.*` | resource | `/dashboard/user/money/credits` | `WorkSuite\Money\CreditController` |
| `dashboard.user.service-requests.index` | GET | `/dashboard/user/service-requests` | `WorkSuite\ServiceRequests\TicketController@index` |
| `dashboard.user.service-requests.close` | PATCH | `/dashboard/user/service-requests/{ticket}/close` | `@close` |
| `dashboard.user.service-requests.reply` | POST | `/dashboard/user/service-requests/{ticket}/reply` | `@reply` |
| `dashboard.user.playbooks.index` | GET | `/dashboard/user/playbooks` | `WorkSuite\Playbooks\PlaybookController@index` |
| `dashboard.user.insights.index` | GET | `/dashboard/user/insights` | `WorkSuite\Insights\InsightController@index` |
| `dashboard.user.team-chat.index` | GET | `/dashboard/user/team-chat` | `WorkSuite\TeamChat\TeamChatController@index` |

## Naming Notes

- All routes follow the existing `dashboard.user.*` convention from `routes/panel.php`.
- WorkCore HR/Team routes use prefix `bos-team` instead of `team` to avoid collision with
  the existing AI collaboration team at `dashboard.user.team.*`.
- Menu items in `WorkCoreBosMenuSeeder` link to these route names.

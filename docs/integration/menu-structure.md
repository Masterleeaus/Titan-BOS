# WorkCore — Menu Structure

Seeded via: `php artisan db:seed --class=WorkCoreBosMenuSeeder`

## Primary Navigation (Cleaning BOS section, order 60–72)

```
Cleaning BOS  [label, order: 60]
│
├── Customers            (wc_customers,     order: 61)  → dashboard.user.customers.index
├── Leads                (wc_leads,         order: 62)  → dashboard.user.leads.index
├── Follow-Ups           (wc_followups,     order: 63)  → dashboard.user.followups.index
├── Jobs                 (wc_jobs,          order: 64)  → dashboard.user.jobs.index
├── Schedule & Dispatch  (wc_schedule,      order: 65)  → dashboard.user.schedule.index
├── Cleaning Checklist   (wc_checklists,    order: 66)  → dashboard.user.checklists.index
│
├── Team ─────────────── (wc_team,          order: 67)  → dashboard.user.bos-team.index
│   ├── Cleaners         (wc_team_cleaners)             → dashboard.user.bos-team.index
│   ├── Availability     (wc_team_availability)         → dashboard.user.bos-team.shifts.index
│   ├── Shift Log        (wc_team_shiftlog)             → dashboard.user.bos-team.attendance.index
│   └── Leave            (wc_team_leave)                → dashboard.user.bos-team.leave.index
│
├── Money ────────────── (wc_money,         order: 68)  → dashboard.user.money.index
│   ├── Quotes           (wc_money_quotes)              → dashboard.user.money.quotes.index
│   ├── Invoices         (wc_money_invoices)            → dashboard.user.money.invoices.index
│   ├── Payments         (wc_money_payments)            → dashboard.user.money.payments.index
│   ├── Expenses         (wc_money_expenses)            → dashboard.user.money.expenses.index
│   └── Credits          (wc_money_credits)             → dashboard.user.money.credits.index
│
├── Service Requests     (wc_service_requests, order: 69) → dashboard.user.service-requests.index
├── Playbooks            (wc_playbooks,     order: 70)  → dashboard.user.playbooks.index
├── Insights             (wc_insights,      order: 71)  → dashboard.user.insights.index
└── Team Chat            (wc_team_chat,     order: 72)  → dashboard.user.team-chat.index
```

## Existing BOS section (unchanged)

```
Business OS  [label, order: 50]
│
└── CRM ─────────────── (crm, order: 51)  → dashboard.crm.dashboard
    ├── CRM Overview    (crm_dashboard)
    ├── Contacts        (crm_contacts)
    ├── Companies       (crm_companies)
    ├── Pipeline        (crm_deals)
    ├── Activities      (crm_activities)
    └── Leads           (crm_leads)
```

## Vertical Language

Surface labels are the cleaning vertical defaults. To change a label, update
`config/verticals.php` under the `cleaning.labels` key. The resolver picks up
the change on next boot without any DB or migration change.

## Menu Model Notes

- Menu items are stored in the `menus` table.
- `MenuService::regenerate()` clears cached menu on seeder run.
- Icons use Tabler icon set (`tabler-*` prefix).
- `firstOrCreate` on `key` prevents duplicate seeding on re-runs.

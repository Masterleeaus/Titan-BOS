<?php

namespace Database\Seeders;

use App\Models\Common\Menu;
use App\Services\Common\MenuService;
use Illuminate\Database\Seeder;

/**
 * Seeds the WorkCore Cleaning BOS navigation into the menu table.
 *
 * Business language uses the cleaning vertical by default.
 * Labels are surface names; backend domain names preserved in keys/routes.
 *
 * Run: php artisan db:seed --class=WorkCoreBosMenuSeeder
 */
class WorkCoreBosMenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->items() as $item) {
            Menu::query()->firstOrCreate(
                ['key' => $item['key']],
                $this->buildAttributes($item)
            );
        }

        // Second pass: wire parent_id now all items exist
        foreach ($this->items() as $item) {
            if (! empty($item['parent_key'])) {
                Menu::query()->where('key', $item['key'])->update([
                    'parent_id' => Menu::query()->where('key', $item['parent_key'])->value('id'),
                ]);
            }
        }

        app(MenuService::class)->regenerate();
    }

    private function buildAttributes(array $item): array
    {
        return [
            'parent_id'      => null,
            'route'          => $item['route'] ?? null,
            'route_slug'     => $item['route_slug'] ?? null,
            'label'          => $item['label'],
            'icon'           => $item['icon'] ?? null,
            'svg'            => $item['svg'] ?? null,
            'order'          => $item['order'] ?? 99,
            'is_active'      => $item['is_active'] ?? true,
            'params'         => $item['params'] ?? [],
            'type'           => $item['type'] ?? 'item',
            'extension'      => null,
            'letter_icon'    => $item['letter_icon'] ?? null,
            'letter_icon_bg' => $item['letter_icon_bg'] ?? null,
        ];
    }

    private function items(): array
    {
        return [
            // ── Section label ─────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_bos_label',
                'route'          => null,
                'label'          => 'Cleaning BOS',
                'type'           => 'label',
                'order'          => 60,
            ],

            // ── Customers ─────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_customers',
                'route'          => 'dashboard.user.customers.index',
                'label'          => 'Customers',
                'icon'           => 'tabler-users',
                'letter_icon'    => 'C',
                'letter_icon_bg' => '#0ea5e9',
                'order'          => 61,
            ],

            // ── Leads ─────────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_leads',
                'route'          => 'dashboard.user.leads.index',
                'label'          => 'Leads',
                'icon'           => 'tabler-user-plus',
                'letter_icon'    => 'L',
                'letter_icon_bg' => '#8b5cf6',
                'order'          => 62,
            ],

            // ── Follow-Ups ────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_followups',
                'route'          => 'dashboard.user.followups.index',
                'label'          => 'Follow-Ups',
                'icon'           => 'tabler-calendar-check',
                'letter_icon'    => 'F',
                'letter_icon_bg' => '#f59e0b',
                'order'          => 63,
            ],

            // ── Jobs ──────────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_jobs',
                'route'          => 'dashboard.user.jobs.index',
                'label'          => 'Jobs',
                'icon'           => 'tabler-briefcase',
                'letter_icon'    => 'J',
                'letter_icon_bg' => '#10b981',
                'order'          => 64,
            ],

            // ── Schedule & Dispatch ───────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_schedule',
                'route'          => 'dashboard.user.schedule.index',
                'label'          => 'Schedule & Dispatch',
                'icon'           => 'tabler-map-pin',
                'letter_icon'    => 'S',
                'letter_icon_bg' => '#f43f5e',
                'order'          => 65,
            ],

            // ── Cleaning Checklist ────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_checklists',
                'route'          => 'dashboard.user.checklists.index',
                'label'          => 'Cleaning Checklist',
                'icon'           => 'tabler-checklist',
                'letter_icon'    => 'K',
                'letter_icon_bg' => '#06b6d4',
                'order'          => 66,
            ],

            // ── Team (parent) ─────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_team',
                'route'          => 'dashboard.user.bos-team.index',
                'label'          => 'Team',
                'icon'           => 'tabler-users-group',
                'letter_icon'    => 'T',
                'letter_icon_bg' => '#6366f1',
                'order'          => 67,
            ],
            // Team children
            [
                'parent_key' => 'wc_team',
                'key'        => 'wc_team_cleaners',
                'route'      => 'dashboard.user.bos-team.index',
                'label'      => 'Cleaners',
                'icon'       => 'tabler-user-check',
                'order'      => 0,
            ],
            [
                'parent_key' => 'wc_team',
                'key'        => 'wc_team_availability',
                'route'      => 'dashboard.user.bos-team.shifts.index',
                'label'      => 'Availability',
                'icon'       => 'tabler-calendar-time',
                'order'      => 1,
            ],
            [
                'parent_key' => 'wc_team',
                'key'        => 'wc_team_shiftlog',
                'route'      => 'dashboard.user.bos-team.attendance.index',
                'label'      => 'Shift Log',
                'icon'       => 'tabler-clock',
                'order'      => 2,
            ],
            [
                'parent_key' => 'wc_team',
                'key'        => 'wc_team_leave',
                'route'      => 'dashboard.user.bos-team.leave.index',
                'label'      => 'Leave',
                'icon'       => 'tabler-beach',
                'order'      => 3,
            ],

            // ── Money (parent) ────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_money',
                'route'          => 'dashboard.user.money.index',
                'label'          => 'Money',
                'icon'           => 'tabler-currency-dollar',
                'letter_icon'    => 'M',
                'letter_icon_bg' => '#22c55e',
                'order'          => 68,
            ],
            // Money children
            [
                'parent_key' => 'wc_money',
                'key'        => 'wc_money_quotes',
                'route'      => 'dashboard.user.money.quotes.index',
                'label'      => 'Quotes',
                'icon'       => 'tabler-file-description',
                'order'      => 0,
            ],
            [
                'parent_key' => 'wc_money',
                'key'        => 'wc_money_invoices',
                'route'      => 'dashboard.user.money.invoices.index',
                'label'      => 'Invoices',
                'icon'       => 'tabler-file-invoice',
                'order'      => 1,
            ],
            [
                'parent_key' => 'wc_money',
                'key'        => 'wc_money_payments',
                'route'      => 'dashboard.user.money.payments.index',
                'label'      => 'Payments',
                'icon'       => 'tabler-credit-card',
                'order'      => 2,
            ],
            [
                'parent_key' => 'wc_money',
                'key'        => 'wc_money_expenses',
                'route'      => 'dashboard.user.money.expenses.index',
                'label'      => 'Expenses',
                'icon'       => 'tabler-receipt',
                'order'      => 3,
            ],
            [
                'parent_key' => 'wc_money',
                'key'        => 'wc_money_credits',
                'route'      => 'dashboard.user.money.credits.index',
                'label'      => 'Credits',
                'icon'       => 'tabler-rosette-discount',
                'order'      => 4,
            ],

            // ── Service Requests ──────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_service_requests',
                'route'          => 'dashboard.user.service-requests.index',
                'label'          => 'Service Requests',
                'icon'           => 'tabler-headset',
                'letter_icon'    => 'R',
                'letter_icon_bg' => '#ef4444',
                'order'          => 69,
            ],

            // ── Playbooks ─────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_playbooks',
                'route'          => 'dashboard.user.playbooks.index',
                'label'          => 'Playbooks',
                'icon'           => 'tabler-book',
                'letter_icon'    => 'P',
                'letter_icon_bg' => '#7c3aed',
                'order'          => 70,
            ],

            // ── Insights ──────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_insights',
                'route'          => 'dashboard.user.insights.index',
                'label'          => 'Insights',
                'icon'           => 'tabler-chart-bar',
                'letter_icon'    => 'I',
                'letter_icon_bg' => '#0284c7',
                'order'          => 71,
            ],

            // ── Team Chat ─────────────────────────────────────────────────
            [
                'parent_key'     => null,
                'key'            => 'wc_team_chat',
                'route'          => 'dashboard.user.team-chat.index',
                'label'          => 'Team Chat',
                'icon'           => 'tabler-messages',
                'letter_icon'    => 'H',
                'letter_icon_bg' => '#64748b',
                'order'          => 72,
            ],
        ];
    }
}

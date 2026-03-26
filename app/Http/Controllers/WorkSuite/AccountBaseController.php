<?php

namespace App\Http\Controllers\WorkSuite;

use App\Http\Controllers\Controller;
use App\Services\VerticalLanguageResolver;
use Illuminate\Support\Facades\Auth;

/**
 * AccountBaseController — WorkCore bridge.
 *
 * WorkSuite controllers all extend AccountBaseController, which:
 *   - Shares $this->user with views
 *   - Shares $this->company with views
 *   - Checks module access via $this->user->modules
 *   - Provides $this->data[] for view data
 *
 * This bridge maps those expectations onto the MagicAI host so that
 * WorkSuite controllers can be ported with minimal changes.
 */
abstract class AccountBaseController extends Controller
{
    /** @var \App\Models\User|null */
    protected $user;

    /** @var \App\Models\Company|null */
    protected $company;

    /** @var string */
    protected string $pageTitle = '';

    /** @var array  Shared view data, mirroring WorkSuite's $this->data pattern. */
    protected array $data = [];

    /** @var VerticalLanguageResolver */
    protected VerticalLanguageResolver $vl;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user    = Auth::user();
            $this->company = function_exists('company') ? company() : null;
            $this->vl      = app(VerticalLanguageResolver::class);

            // Expose to all views, mirroring WorkSuite's view()->share() calls.
            view()->share('authUser', $this->user);
            view()->share('company',  $this->company);
            view()->share('vl',       $this->vl);

            // Populate base data bag.
            $this->data['user']      = $this->user;
            $this->data['company']   = $this->company;
            $this->data['pageTitle'] = $this->pageTitle;

            return $next($request);
        });
    }

    /**
     * Check module access — mirrors WorkSuite's:
     *   abort_403(!in_array('employees', $this->user->modules))
     *
     * Phase 2: replace with proper module-flag lookup from DB.
     */
    protected function requireModule(string $module): void
    {
        // Stub — all modules enabled during scaffold phase.
        // TODO: check against a modules table or config flag.
    }

    /**
     * Check permission — mirrors WorkSuite's:
     *   $viewPermission = user()->permission('view_invoices')
     *   abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']))
     *
     * Phase 2: implement granular permission lookup.
     * For now, any authenticated user passes.
     */
    protected function checkPermission(string $permission, array $allowed = ['all']): void
    {
        // Stub — open during scaffold phase.
        // TODO: resolve via Spatie permissions or a custom permission table.
    }

    /**
     * Merge extra data into $this->data and return a view.
     * Mirrors WorkSuite's DataTable render pattern:
     *   return $dataTable->render('invoices.index', $this->data);
     */
    protected function view(string $view, array $extra = []): \Illuminate\View\View
    {
        return view($view, array_merge($this->data, $extra));
    }

    /**
     * Resolve a vertical surface label.
     */
    protected function label(string $key, ?string $fallback = null): string
    {
        return $this->vl->label($key, $fallback);
    }

    /**
     * Merge vertical labels into a data array for view sharing.
     */
    protected function withLabels(array $data = []): array
    {
        return array_merge($this->data, $data, ['vl' => $this->vl]);
    }
}

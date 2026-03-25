<?php

namespace App\Http\Controllers\WorkSuite;

use App\Http\Controllers\Controller;
use App\Services\VerticalLanguageResolver;

abstract class WorkSuiteBaseController extends Controller
{
    protected VerticalLanguageResolver $vl;

    public function __construct()
    {
        $this->vl = app(VerticalLanguageResolver::class);
    }

    /**
     * Resolve a vertical label for use in views.
     */
    protected function label(string $key, ?string $fallback = null): string
    {
        return $this->vl->label($key, $fallback);
    }

    /**
     * Share vertical labels with a view.
     */
    protected function withLabels(array $data = []): array
    {
        return array_merge($data, ['vl' => $this->vl]);
    }
}

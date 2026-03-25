<?php

return [
    'feature_prefix' => env('WORKSUITE_FEATURE_PREFIX', 'worksuite'),
    'route_prefix' => env('WORKSUITE_ROUTE_PREFIX', 'worksuite'),
    'middleware' => ['auth'],
    'features' => [
        'crm' => true,
        'projects' => true,
        'tasks' => true,
        'finance' => true,
        'hr' => true,
        'tickets' => true,
    ],
    'tenancy' => [
        'tenant_column' => env('WORKSUITE_TENANT_COLUMN', 'company_id'),
        'legacy_team_column' => env('WORKSUITE_LEGACY_TEAM_COLUMN', 'team_id'),
    ],
    'imports' => [
        'controllers_namespace' => 'App\Http\Controllers\WorkSuite',
        'models_namespace' => 'App\Models\WorkSuite',
        'services_namespace' => 'App\Services\WorkSuite',
        'views_path' => 'worksuite',
        'routes_file' => 'routes/worksuite.php',
    ],
];

<?php

return [
    // Map abilities -> your system's permission slugs.
    // Adjust these to match your Worksuite's permission names.
    'abilities' => [
        'viewAny' => 'contractor.view',
        'view'    => 'contractor.view',
        'create'  => 'contractor.create',
        'update'  => 'contractor.update',
        'delete'  => 'contractor.delete',
        'manage'  => 'contractor.manage', // super permission
    ],

    // If using spatie/laravel-permission, these will be auto-created on boot (guarded).
    'auto_seed_permissions' => true,
];

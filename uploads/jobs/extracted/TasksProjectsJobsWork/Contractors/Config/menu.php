<?php

return [
    // Choose how to register the sidebar item in your Worksuite build.
    // 'app_menu' uses App\Menu::add([...])
    // 'container_menu' uses app('menu')->add([...])
    // 'none' skips auto-registration (you can add manually)
    'driver' => 'app_menu',

    // Basic menu item definition
    'item' => [
        'slug' => 'contractors',
        'title' => 'Contractors',
        'icon' => 'fa fa-user-tie',
        'route' => 'contractor.index',
        'permission' => 'contractor.manage',
    ],
];

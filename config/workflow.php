<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('WORKFLOW_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    |
    | Tables that store workflow instances, the transition history and the
    | approval steps. Override the names if they collide with your schema.
    |
    */
    'tables' => [
        'instances' => 'workflow_instances',
        'transitions' => 'workflow_transitions',
        'approvals' => 'workflow_approvals',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin UI (Filament)
    |--------------------------------------------------------------------------
    |
    | The optional Filament panel that lists pending approvals and renders the
    | transition history. Disable it entirely, or run it behind your own
    | middleware stack and route prefix.
    |
    */
    'ui' => [
        'enabled' => (bool) env('WORKFLOW_UI_ENABLED', true),
        'prefix' => env('WORKFLOW_UI_PREFIX', 'workflow'),
        'middleware' => ['web', 'auth'],
    ],
];

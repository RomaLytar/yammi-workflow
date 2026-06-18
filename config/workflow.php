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
        'workflows' => 'workflows',
        'states' => 'workflow_states',
        'transition_defs' => 'workflow_transition_defs',
        'instances' => 'workflow_instances',
        'transitions' => 'workflow_transitions',
        'approvals' => 'workflow_approvals',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subjects
    |--------------------------------------------------------------------------
    |
    | Maps a host Eloquent model class to the workflow it runs on (by key).
    | A model using the HasWorkflow trait resolves its process through this map.
    |
    |   \App\Models\Invoice::class => 'invoice',
    |
    */
    'subjects' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | State storage
    |--------------------------------------------------------------------------
    |
    | Where the current state of a subject is kept. "instance" tracks it in the
    | workflow_instances table (host schema untouched); "column" keeps it in a
    | column on the host model named below.
    |
    */
    'state_store' => [
        'driver' => env('WORKFLOW_STATE_STORE', 'instance'),
        'column' => 'workflow_state',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Gate ability consulted before a user-initiated transition. Define it in
    | your app to restrict who may move a subject; when it is not defined, or
    | the transition runs without an authenticated user (jobs, commands), the
    | transition is allowed.
    |
    */
    'authorization' => [
        'ability' => 'workflow.transition',
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

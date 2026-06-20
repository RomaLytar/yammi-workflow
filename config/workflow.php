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
    | Multi-tenancy
    |--------------------------------------------------------------------------
    |
    | Bind Yammi\Workflow\Application\Contract\TenantResolver to your own
    | implementation to scope every workflow, instance, transition and
    | approval to the current tenant. When the resolver returns null
    | (the default), the package behaves as a single-tenant app.
    |
    */
    'tenancy' => [
        'column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | When a step becomes the current one, its assignee is notified. Turn it
    | off entirely, or choose the channels the notification is sent on (any
    | Laravel notification channel; 'database' needs the notifications table).
    |
    */
    'notifications' => [
        'enabled' => (bool) env('WORKFLOW_NOTIFICATIONS', true),
        'channels' => ['database'],
    ],
];

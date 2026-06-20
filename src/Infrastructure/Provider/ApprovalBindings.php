<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\ApprovalInbox;
use Yammi\Workflow\Application\Contract\ApprovalNotifier;
use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Application\Contract\TransitionApprovalGate;
use Yammi\Workflow\Infrastructure\Api\ApprovalManager;
use Yammi\Workflow\Infrastructure\Notification\LaravelApprovalNotifier;
use Yammi\Workflow\Infrastructure\Notification\NullApprovalNotifier;
use Yammi\Workflow\Infrastructure\Persistence\Approval\EloquentApprovalGate;
use Yammi\Workflow\Infrastructure\Persistence\Approval\EloquentApprovalInbox;
use Yammi\Workflow\Infrastructure\Persistence\Approval\EloquentApprovalStore;

/**
 * @internal
 */
final class ApprovalBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(ApprovalStore::class, EloquentApprovalStore::class);
        $this->app->bind(ApprovalInbox::class, EloquentApprovalInbox::class);
        $this->app->bind(TransitionApprovalGate::class, EloquentApprovalGate::class);
        $this->app->bind(ApprovalNotifier::class, static fn (Application $app): ApprovalNotifier => (bool) config('workflow.notifications.enabled', true)
            ? $app->make(LaravelApprovalNotifier::class)
            : $app->make(NullApprovalNotifier::class));
        $this->app->singleton(ApprovalManager::class);
    }
}

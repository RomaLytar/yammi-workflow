<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\ApprovalStore;
use Yammi\Workflow\Infrastructure\Api\ApprovalManager;
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
        $this->app->singleton(ApprovalManager::class);
    }
}

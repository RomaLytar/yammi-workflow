<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\TransitionHistory;
use Yammi\Workflow\Infrastructure\Api\WorkflowManager;
use Yammi\Workflow\Infrastructure\Persistence\Query\EloquentTransitionHistory;

/**
 * @internal
 */
final class ReadBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(TransitionHistory::class, EloquentTransitionHistory::class);
        $this->app->singleton(WorkflowManager::class);
    }
}

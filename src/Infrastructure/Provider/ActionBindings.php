<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\ActionCatalog;
use Yammi\Workflow\Application\Contract\TransitionActionRunner;
use Yammi\Workflow\Infrastructure\Action\InMemoryActionCatalog;
use Yammi\Workflow\Infrastructure\Persistence\Action\EloquentTransitionActionRunner;

/**
 * @internal
 */
final class ActionBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->singleton(InMemoryActionCatalog::class);
        $this->app->alias(InMemoryActionCatalog::class, ActionCatalog::class);
        $this->app->bind(TransitionActionRunner::class, EloquentTransitionActionRunner::class);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\ActorResolver;
use Yammi\Workflow\Application\Contract\TransitionRecorder;
use Yammi\Workflow\Application\Contract\WorkflowEventDispatcher;
use Yammi\Workflow\Infrastructure\Actor\AuthActorResolver;
use Yammi\Workflow\Infrastructure\Event\LaravelWorkflowEventDispatcher;
use Yammi\Workflow\Infrastructure\Persistence\Recorder\EloquentTransitionRecorder;

/**
 * @internal
 */
final class HistoryBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(TransitionRecorder::class, EloquentTransitionRecorder::class);
        $this->app->bind(WorkflowEventDispatcher::class, LaravelWorkflowEventDispatcher::class);
        $this->app->bind(ActorResolver::class, AuthActorResolver::class);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Event;

use Illuminate\Contracts\Events\Dispatcher;
use Yammi\Workflow\Application\Contract\WorkflowEventDispatcher;

/**
 * @internal
 */
final class LaravelWorkflowEventDispatcher implements WorkflowEventDispatcher
{
    public function __construct(
        private readonly Dispatcher $events,
    ) {}

    public function dispatch(object $event): void
    {
        $this->events->dispatch($event);
    }
}

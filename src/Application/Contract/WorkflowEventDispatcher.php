<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface WorkflowEventDispatcher
{
    public function dispatch(object $event): void;
}

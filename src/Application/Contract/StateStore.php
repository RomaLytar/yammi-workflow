<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Domain\Workflow\ValueObject\State;

interface StateStore
{
    public function current(object $subject): ?State;

    public function put(object $subject, State $state, int $workflowId): void;

    public function pinnedWorkflowId(object $subject): ?int;
}

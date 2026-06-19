<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TransitionConditionChecker
{
    public function satisfied(int $workflowId, string $from, string $to, object $subject): bool;
}

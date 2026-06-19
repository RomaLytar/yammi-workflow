<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TransitionApprovalGate
{
    public function check(int $workflowId, string $from, string $to, object $subject): void;
}

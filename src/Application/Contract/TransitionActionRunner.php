<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TransitionActionRunner
{
    public function run(int $workflowId, string $from, string $to, object $subject): void;
}

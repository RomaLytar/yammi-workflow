<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface HookRegistry
{
    public function run(string $workflow, object $subject, string $from, string $to): void;
}

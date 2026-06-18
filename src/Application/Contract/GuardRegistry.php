<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface GuardRegistry
{
    public function allows(string $workflow, object $subject, string $from, string $to): bool;
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TransitionGuard
{
    public function passes(object $subject, string $from, string $to): bool;
}

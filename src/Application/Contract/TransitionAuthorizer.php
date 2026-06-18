<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TransitionAuthorizer
{
    public function allows(object $subject, string $from, string $to): bool;
}

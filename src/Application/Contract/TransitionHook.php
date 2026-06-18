<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TransitionHook
{
    public function run(object $subject, string $from, string $to): void;
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface WorkflowKeyResolver
{
    public function keyFor(object $subject): string;
}

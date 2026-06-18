<?php

declare(strict_types=1);

namespace Yammi\Workflow\Events;

use Yammi\Workflow\Application\DTO\ActorData;

final class WorkflowTransitioned
{
    public function __construct(
        public readonly object $subject,
        public readonly string $workflow,
        public readonly string $from,
        public readonly string $to,
        public readonly ?ActorData $actor,
    ) {}
}

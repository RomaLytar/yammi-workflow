<?php

declare(strict_types=1);

namespace Yammi\Workflow\Events;

use Yammi\Workflow\Application\DTO\ActorData;

final class WorkflowTransitioned
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly object $subject,
        public readonly string $workflow,
        public readonly string $from,
        public readonly string $to,
        public readonly ?ActorData $actor,
        public readonly ?string $reason = null,
        public readonly array $meta = [],
    ) {}
}

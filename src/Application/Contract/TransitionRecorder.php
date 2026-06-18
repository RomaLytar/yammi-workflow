<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\ActorData;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

interface TransitionRecorder
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(
        object $subject,
        int $workflowId,
        State $from,
        State $to,
        ?ActorData $actor,
        ?string $reason = null,
        array $meta = [],
    ): void;
}

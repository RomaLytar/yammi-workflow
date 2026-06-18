<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\ActorData;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

interface TransitionRecorder
{
    public function record(
        object $subject,
        string $workflow,
        State $from,
        State $to,
        ?ActorData $actor,
    ): void;
}

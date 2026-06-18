<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use DomainException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class TransitionBlockedException extends DomainException
{
    public static function forTransition(string $workflow, State $from, State $to): self
    {
        return new self(sprintf(
            'Transition from "%s" to "%s" in workflow "%s" was blocked by a guard.',
            $from,
            $to,
            $workflow,
        ));
    }
}

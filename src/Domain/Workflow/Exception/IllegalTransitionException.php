<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use DomainException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class IllegalTransitionException extends DomainException
{
    public static function notAllowed(State $from, State $to): self
    {
        return new self(sprintf('Transition from "%s" to "%s" is not allowed.', $from, $to));
    }

    public static function unknownState(State $state): self
    {
        return new self(sprintf('State "%s" is not part of this workflow.', $state));
    }
}

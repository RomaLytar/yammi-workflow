<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use RuntimeException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class UnauthorizedTransitionException extends RuntimeException
{
    public static function forTransition(string $workflow, State $from, State $to): self
    {
        return new self(sprintf(
            'The current actor is not authorized to move "%s" from "%s" to "%s".',
            $workflow,
            $from,
            $to,
        ));
    }
}

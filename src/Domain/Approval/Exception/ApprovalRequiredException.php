<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Approval\Exception;

use RuntimeException;

final class ApprovalRequiredException extends RuntimeException
{
    public static function forTransition(string $from, string $to): self
    {
        return new self(sprintf(
            'Transition from "%s" to "%s" requires approval that is not yet complete.',
            $from,
            $to,
        ));
    }
}

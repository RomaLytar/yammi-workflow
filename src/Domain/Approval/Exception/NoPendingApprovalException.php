<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Approval\Exception;

use RuntimeException;

final class NoPendingApprovalException extends RuntimeException
{
    public static function forSubject(string $type): self
    {
        return new self(sprintf('There is no pending approval step for "%s".', $type));
    }
}

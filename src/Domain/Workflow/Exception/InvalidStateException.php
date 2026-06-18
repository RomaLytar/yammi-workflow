<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use DomainException;

final class InvalidStateException extends DomainException
{
    public static function empty(): self
    {
        return new self('A workflow state name cannot be empty.');
    }
}

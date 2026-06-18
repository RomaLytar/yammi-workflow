<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use DomainException;

final class WorkflowNotFoundException extends DomainException
{
    public static function withKey(string $key): self
    {
        return new self(sprintf('No workflow definition found for key "%s".', $key));
    }
}

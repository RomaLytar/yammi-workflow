<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use DomainException;

final class SubjectNotMappedException extends DomainException
{
    public static function forClass(string $class): self
    {
        return new self(sprintf(
            'Model "%s" is not mapped to any workflow. Add it to the "workflow.subjects" config.',
            $class,
        ));
    }
}

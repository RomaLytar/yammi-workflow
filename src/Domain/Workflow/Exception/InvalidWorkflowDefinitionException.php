<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Exception;

use DomainException;

final class InvalidWorkflowDefinitionException extends DomainException
{
    public static function noStates(): self
    {
        return new self('A workflow definition must declare at least one state.');
    }

    public static function unknownTransitionState(string $name): self
    {
        return new self(sprintf('Transition references state "%s" which is not declared.', $name));
    }

    public static function unknownInitialState(string $name): self
    {
        return new self(sprintf('Initial state "%s" is not one of the declared states.', $name));
    }
}

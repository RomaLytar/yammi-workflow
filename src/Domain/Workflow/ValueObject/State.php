<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\ValueObject;

use Yammi\Workflow\Domain\Workflow\Exception\InvalidStateException;

final class State
{
    public readonly string $name;

    public function __construct(string $name)
    {
        $trimmed = trim($name);

        if ($trimmed === '') {
            throw InvalidStateException::empty();
        }

        $this->name = $trimmed;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

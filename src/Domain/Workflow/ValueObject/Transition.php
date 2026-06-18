<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\ValueObject;

final class Transition
{
    public function __construct(
        public readonly State $from,
        public readonly State $to,
    ) {}

    public function matches(State $from, State $to): bool
    {
        return $this->from->equals($from) && $this->to->equals($to);
    }

    public function startsFrom(State $from): bool
    {
        return $this->from->equals($from);
    }
}

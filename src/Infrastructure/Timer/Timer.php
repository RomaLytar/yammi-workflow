<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Timer;

/**
 * @internal
 */
final class Timer
{
    public function __construct(
        public readonly string $workflow,
        public readonly string $state,
        public readonly int $seconds,
        public readonly string $target,
    ) {}
}

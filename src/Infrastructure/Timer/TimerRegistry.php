<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Timer;

final class TimerRegistry
{
    /** @var list<Timer> */
    private array $timers = [];

    public function after(string $workflow, string $state, int $seconds, string $target): void
    {
        $this->timers[] = new Timer($workflow, $state, $seconds, $target);
    }

    /**
     * @return list<Timer>
     */
    public function all(): array
    {
        return $this->timers;
    }
}

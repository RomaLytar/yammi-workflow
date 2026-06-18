<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Guard;

use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Application\Contract\TransitionGuard;

final class InMemoryGuardRegistry implements GuardRegistry
{
    /** @var array<string, list<TransitionGuard>> */
    private array $guards = [];

    public function register(string $workflow, TransitionGuard $guard): void
    {
        $this->guards[$workflow][] = $guard;
    }

    /**
     * @param  callable(object, string, string): bool  $callback
     */
    public function registerUsing(string $workflow, callable $callback): void
    {
        $this->guards[$workflow][] = new CallableGuard($callback);
    }

    public function allows(string $workflow, object $subject, string $from, string $to): bool
    {
        foreach ($this->guards[$workflow] ?? [] as $guard) {
            if (! $guard->passes($subject, $from, $to)) {
                return false;
            }
        }

        return true;
    }
}

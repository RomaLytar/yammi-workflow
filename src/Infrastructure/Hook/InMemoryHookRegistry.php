<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Hook;

use Yammi\Workflow\Application\Contract\HookRegistry;
use Yammi\Workflow\Application\Contract\TransitionHook;

final class InMemoryHookRegistry implements HookRegistry
{
    /** @var array<string, list<TransitionHook>> */
    private array $onTransition = [];

    /** @var array<string, array<string, list<TransitionHook>>> */
    private array $onEnter = [];

    /** @var array<string, array<string, list<TransitionHook>>> */
    private array $onLeave = [];

    /**
     * @param  callable(object, string, string): void|TransitionHook  $hook
     */
    public function onTransition(string $workflow, callable|TransitionHook $hook): void
    {
        $this->onTransition[$workflow][] = $this->normalize($hook);
    }

    /**
     * @param  callable(object, string, string): void|TransitionHook  $hook
     */
    public function onEnter(string $workflow, string $state, callable|TransitionHook $hook): void
    {
        $this->onEnter[$workflow][$state][] = $this->normalize($hook);
    }

    /**
     * @param  callable(object, string, string): void|TransitionHook  $hook
     */
    public function onLeave(string $workflow, string $state, callable|TransitionHook $hook): void
    {
        $this->onLeave[$workflow][$state][] = $this->normalize($hook);
    }

    public function run(string $workflow, object $subject, string $from, string $to): void
    {
        foreach ($this->onLeave[$workflow][$from] ?? [] as $hook) {
            $hook->run($subject, $from, $to);
        }

        foreach ($this->onTransition[$workflow] ?? [] as $hook) {
            $hook->run($subject, $from, $to);
        }

        foreach ($this->onEnter[$workflow][$to] ?? [] as $hook) {
            $hook->run($subject, $from, $to);
        }
    }

    private function normalize(callable|TransitionHook $hook): TransitionHook
    {
        return $hook instanceof TransitionHook ? $hook : new CallableHook($hook);
    }
}

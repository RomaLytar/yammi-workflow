<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow;

use Yammi\Workflow\Domain\Workflow\Exception\InvalidWorkflowDefinitionException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Domain\Workflow\ValueObject\Transition;

final class ArrayWorkflowDefinition implements WorkflowDefinition
{
    /**
     * @param  array<string, State>  $states
     * @param  list<Transition>  $transitions
     */
    private function __construct(
        private readonly array $states,
        private readonly array $transitions,
        private readonly State $initial,
    ) {}

    /**
     * @param  list<string>  $states
     * @param  array<string, list<string>>  $transitions
     */
    public static function fromArray(array $states, array $transitions, ?string $initial = null): self
    {
        if ($states === []) {
            throw InvalidWorkflowDefinitionException::noStates();
        }

        $indexed = [];

        foreach ($states as $name) {
            $state = new State($name);
            $indexed[$state->name] = $state;
        }

        $initialState = new State($initial ?? $states[0]);

        if (! isset($indexed[$initialState->name])) {
            throw InvalidWorkflowDefinitionException::unknownInitialState($initialState->name);
        }

        $edges = [];

        foreach ($transitions as $from => $targets) {
            $fromState = new State((string) $from);

            if (! isset($indexed[$fromState->name])) {
                throw InvalidWorkflowDefinitionException::unknownTransitionState($fromState->name);
            }

            foreach ($targets as $target) {
                $toState = new State($target);

                if (! isset($indexed[$toState->name])) {
                    throw InvalidWorkflowDefinitionException::unknownTransitionState($toState->name);
                }

                $edges[] = new Transition($indexed[$fromState->name], $indexed[$toState->name]);
            }
        }

        return new self($indexed, $edges, $initialState);
    }

    public function states(): array
    {
        return array_values($this->states);
    }

    public function initialState(): State
    {
        return $this->initial;
    }

    public function hasState(State $state): bool
    {
        return isset($this->states[$state->name]);
    }

    public function allows(State $from, State $to): bool
    {
        foreach ($this->transitions as $transition) {
            if ($transition->matches($from, $to)) {
                return true;
            }
        }

        return false;
    }

    public function allowedTransitions(State $from): array
    {
        $reachable = [];

        foreach ($this->transitions as $transition) {
            if ($transition->startsFrom($from)) {
                $reachable[$transition->to->name] = $transition->to;
            }
        }

        return array_values($reachable);
    }
}

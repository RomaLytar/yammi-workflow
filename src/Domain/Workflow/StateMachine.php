<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow;

use Yammi\Workflow\Domain\Workflow\Exception\IllegalTransitionException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class StateMachine
{
    public function __construct(
        private readonly WorkflowDefinition $definition,
    ) {}

    public function can(State $from, State $to): bool
    {
        return $this->definition->hasState($from)
            && $this->definition->hasState($to)
            && $this->definition->allows($from, $to);
    }

    public function transition(State $from, State $to): State
    {
        if (! $this->definition->hasState($from)) {
            throw IllegalTransitionException::unknownState($from);
        }

        if (! $this->definition->hasState($to)) {
            throw IllegalTransitionException::unknownState($to);
        }

        if (! $this->definition->allows($from, $to)) {
            throw IllegalTransitionException::notAllowed($from, $to);
        }

        return $to;
    }

    /**
     * @return list<State>
     */
    public function allowedTransitions(State $from): array
    {
        return $this->definition->allowedTransitions($from);
    }
}

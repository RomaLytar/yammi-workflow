<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow;

use Yammi\Workflow\Domain\Workflow\ValueObject\State;

interface WorkflowDefinition
{
    /**
     * @return list<State>
     */
    public function states(): array;

    public function initialState(): State;

    public function hasState(State $state): bool;

    public function allows(State $from, State $to): bool;

    /**
     * @return list<State>
     */
    public function allowedTransitions(State $from): array;
}

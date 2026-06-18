<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\TransitionHistory;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Application\DTO\WorkflowSnapshotData;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class SnapshotWorkflowAction
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly StateStore $states,
        private readonly WorkflowKeyResolver $keys,
        private readonly TransitionHistory $history,
    ) {}

    public function __invoke(object $subject): WorkflowSnapshotData
    {
        $key = $this->keys->keyFor($subject);
        $definition = $this->definitions->find($key);
        $current = $this->states->current($subject) ?? $definition->initialState();

        $allowed = array_map(
            static fn (State $state): string => $state->name,
            (new StateMachine($definition))->allowedTransitions($current),
        );

        return new WorkflowSnapshotData(
            $key,
            $current->name,
            $allowed,
            $this->history->forSubject($subject),
        );
    }
}

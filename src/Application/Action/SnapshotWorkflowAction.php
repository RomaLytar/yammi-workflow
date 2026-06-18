<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\TransitionAuthorizer;
use Yammi\Workflow\Application\Contract\TransitionHistory;
use Yammi\Workflow\Application\DTO\WorkflowSnapshotData;
use Yammi\Workflow\Application\Service\SubjectDefinitionResolver;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class SnapshotWorkflowAction
{
    public function __construct(
        private readonly SubjectDefinitionResolver $resolver,
        private readonly StateStore $states,
        private readonly TransitionHistory $history,
        private readonly GuardRegistry $guards,
        private readonly TransitionAuthorizer $authorizer,
    ) {}

    public function __invoke(object $subject): WorkflowSnapshotData
    {
        $resolved = $this->resolver->resolve($subject);
        $current = $this->states->current($subject) ?? $resolved->definition->initialState();

        $reachable = array_filter(
            (new StateMachine($resolved->definition))->allowedTransitions($current),
            fn (State $target): bool => $this->guards->allows($resolved->key, $subject, $current->name, $target->name)
                && $this->authorizer->allows($subject, $current->name, $target->name),
        );

        $allowed = array_values(array_map(
            static fn (State $state): string => $state->name,
            $reachable,
        ));

        return new WorkflowSnapshotData(
            $resolved->key,
            $current->name,
            $allowed,
            $this->history->forSubject($subject),
        );
    }
}

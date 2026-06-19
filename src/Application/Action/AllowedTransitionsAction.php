<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\TransitionAuthorizer;
use Yammi\Workflow\Application\Contract\TransitionConditionChecker;
use Yammi\Workflow\Application\Service\SubjectDefinitionResolver;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class AllowedTransitionsAction
{
    public function __construct(
        private readonly SubjectDefinitionResolver $resolver,
        private readonly StateStore $states,
        private readonly GuardRegistry $guards,
        private readonly TransitionAuthorizer $authorizer,
        private readonly TransitionConditionChecker $conditions,
    ) {}

    /**
     * @return list<State>
     */
    public function __invoke(object $subject): array
    {
        $resolved = $this->resolver->resolve($subject);
        $current = $this->states->current($subject) ?? $resolved->definition->initialState();

        $allowed = (new StateMachine($resolved->definition))->allowedTransitions($current);

        return array_values(array_filter(
            $allowed,
            fn (State $target): bool => $this->guards->allows($resolved->key, $subject, $current->name, $target->name)
                && $this->conditions->satisfied($resolved->workflowId, $current->name, $target->name, $subject)
                && $this->authorizer->allows($subject, $current->name, $target->name),
        ));
    }
}

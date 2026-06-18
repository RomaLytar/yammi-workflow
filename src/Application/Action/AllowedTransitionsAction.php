<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class AllowedTransitionsAction
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly StateStore $states,
        private readonly WorkflowKeyResolver $keys,
        private readonly GuardRegistry $guards,
    ) {}

    /**
     * @return list<State>
     */
    public function __invoke(object $subject): array
    {
        $key = $this->keys->keyFor($subject);
        $definition = $this->definitions->find($key);
        $current = $this->states->current($subject) ?? $definition->initialState();

        $allowed = (new StateMachine($definition))->allowedTransitions($current);

        return array_values(array_filter(
            $allowed,
            fn (State $target): bool => $this->guards->allows($key, $subject, $current->name, $target->name),
        ));
    }
}

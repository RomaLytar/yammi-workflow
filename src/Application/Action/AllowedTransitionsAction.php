<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

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
    ) {}

    /**
     * @return list<State>
     */
    public function __invoke(object $subject): array
    {
        $definition = $this->definitions->find($this->keys->keyFor($subject));
        $current = $this->states->current($subject) ?? $definition->initialState();

        return (new StateMachine($definition))->allowedTransitions($current);
    }
}

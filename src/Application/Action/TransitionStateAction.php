<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Application\DTO\TransitionResultData;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class TransitionStateAction
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly StateStore $states,
        private readonly WorkflowKeyResolver $keys,
    ) {}

    public function __invoke(object $subject, string $to): TransitionResultData
    {
        $key = $this->keys->keyFor($subject);
        $definition = $this->definitions->find($key);

        $current = $this->states->current($subject) ?? $definition->initialState();
        $next = (new StateMachine($definition))->transition($current, new State($to));

        $this->states->put($subject, $next);

        return new TransitionResultData($key, $current->name, $next->name);
    }
}

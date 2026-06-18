<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class CurrentStateAction
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly StateStore $states,
        private readonly WorkflowKeyResolver $keys,
    ) {}

    public function __invoke(object $subject): State
    {
        $definition = $this->definitions->find($this->keys->keyFor($subject));

        return $this->states->current($subject) ?? $definition->initialState();
    }
}

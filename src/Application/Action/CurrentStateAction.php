<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Service\SubjectDefinitionResolver;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class CurrentStateAction
{
    public function __construct(
        private readonly SubjectDefinitionResolver $resolver,
        private readonly StateStore $states,
    ) {}

    public function __invoke(object $subject): State
    {
        $definition = $this->resolver->resolve($subject)->definition;

        return $this->states->current($subject) ?? $definition->initialState();
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Service;

use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;

final class WorkflowDiagram
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
    ) {}

    public function mermaid(string $key): string
    {
        $definition = $this->definitions->find($key);

        $lines = ['stateDiagram-v2', '    [*] --> '.$definition->initialState()->name];

        foreach ($definition->states() as $state) {
            foreach ($definition->allowedTransitions($state) as $target) {
                $lines[] = '    '.$state->name.' --> '.$target->name;
            }
        }

        return implode("\n", $lines);
    }
}

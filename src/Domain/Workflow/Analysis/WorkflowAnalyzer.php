<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Analysis;

use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Domain\Workflow\WorkflowDefinition;

final class WorkflowAnalyzer
{
    public function analyze(WorkflowDefinition $definition): WorkflowAnalysis
    {
        $reachable = $this->reachable($definition);

        $unreachable = array_values(array_filter(
            array_map(static fn (State $state): string => $state->name, $definition->states()),
            static fn (string $name): bool => ! isset($reachable[$name]),
        ));

        return new WorkflowAnalysis($unreachable, $this->hasTerminalState($definition));
    }

    /**
     * @return array<string, true>
     */
    private function reachable(WorkflowDefinition $definition): array
    {
        $seen = [];
        $queue = [$definition->initialState()];

        while ($queue !== []) {
            $state = array_shift($queue);

            if (isset($seen[$state->name])) {
                continue;
            }

            $seen[$state->name] = true;

            foreach ($definition->allowedTransitions($state) as $next) {
                $queue[] = $next;
            }
        }

        return $seen;
    }

    private function hasTerminalState(WorkflowDefinition $definition): bool
    {
        foreach ($definition->states() as $state) {
            if ($definition->allowedTransitions($state) === []) {
                return true;
            }
        }

        return false;
    }
}

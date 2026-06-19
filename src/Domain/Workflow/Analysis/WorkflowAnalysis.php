<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Workflow\Analysis;

final class WorkflowAnalysis
{
    /**
     * @param  list<string>  $unreachable
     */
    public function __construct(
        public readonly array $unreachable,
        public readonly bool $hasTerminalState,
    ) {}

    public function isHealthy(): bool
    {
        return $this->unreachable === [] && $this->hasTerminalState;
    }

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        $messages = array_map(
            static fn (string $state): string => sprintf('State "%s" is unreachable from the initial state.', $state),
            $this->unreachable,
        );

        if (! $this->hasTerminalState) {
            $messages[] = 'The workflow has no terminal state — every state can move on, so it never ends.';
        }

        return $messages;
    }
}

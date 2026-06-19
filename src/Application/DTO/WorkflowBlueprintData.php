<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class WorkflowBlueprintData
{
    /**
     * @param  list<string>  $states
     * @param  array<string, list<string>>  $transitions
     * @param  array<string, list<array{field: string, operator: string, value: string}>>  $conditions  keyed by "from>to"
     * @param  array<string, list<string>>  $actions  action names keyed by "from>to"
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly array $states,
        public readonly array $transitions,
        public readonly string $initial,
        public readonly array $conditions = [],
        public readonly array $actions = [],
    ) {}

    /**
     * @param  array{key: string, name?: string, states: list<string>, transitions?: array<string, list<string>>, initial?: string, conditions?: array<string, list<array{field: string, operator: string, value: string}>>, actions?: array<string, list<string>>}  $spec
     */
    public static function fromArray(array $spec): self
    {
        $states = $spec['states'];

        return new self(
            $spec['key'],
            $spec['name'] ?? $spec['key'],
            $states,
            $spec['transitions'] ?? [],
            $spec['initial'] ?? ($states[0] ?? ''),
            $spec['conditions'] ?? [],
            $spec['actions'] ?? [],
        );
    }
}

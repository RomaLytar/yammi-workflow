<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class WorkflowBlueprintData
{
    /**
     * @param  list<string>  $states
     * @param  array<string, list<string>>  $transitions
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly array $states,
        public readonly array $transitions,
        public readonly string $initial,
    ) {}

    /**
     * @param  array{key: string, name?: string, states: list<string>, transitions?: array<string, list<string>>, initial?: string}  $spec
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
        );
    }
}

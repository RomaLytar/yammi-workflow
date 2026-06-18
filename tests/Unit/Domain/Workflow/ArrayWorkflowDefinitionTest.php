<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Domain\Workflow;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Domain\Workflow\ArrayWorkflowDefinition;
use Yammi\Workflow\Domain\Workflow\Exception\InvalidWorkflowDefinitionException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class ArrayWorkflowDefinitionTest extends TestCase
{
    private function invoiceDefinition(): ArrayWorkflowDefinition
    {
        return ArrayWorkflowDefinition::fromArray(
            ['draft', 'pending', 'approved', 'rejected'],
            [
                'draft' => ['pending'],
                'pending' => ['approved', 'rejected'],
            ],
        );
    }

    public function test_it_lists_declared_states(): void
    {
        $names = array_map(
            static fn (State $state): string => $state->name,
            $this->invoiceDefinition()->states(),
        );

        $this->assertSame(['draft', 'pending', 'approved', 'rejected'], $names);
    }

    public function test_it_knows_which_states_it_contains(): void
    {
        $definition = $this->invoiceDefinition();

        $this->assertTrue($definition->hasState(new State('pending')));
        $this->assertFalse($definition->hasState(new State('paid')));
    }

    public function test_it_allows_declared_transitions(): void
    {
        $definition = $this->invoiceDefinition();

        $this->assertTrue($definition->allows(new State('draft'), new State('pending')));
        $this->assertTrue($definition->allows(new State('pending'), new State('rejected')));
    }

    public function test_it_disallows_undeclared_transitions(): void
    {
        $definition = $this->invoiceDefinition();

        $this->assertFalse($definition->allows(new State('approved'), new State('draft')));
        $this->assertFalse($definition->allows(new State('draft'), new State('approved')));
    }

    public function test_it_lists_reachable_states_without_duplicates(): void
    {
        $definition = ArrayWorkflowDefinition::fromArray(
            ['a', 'b', 'c'],
            ['a' => ['b', 'c', 'b']],
        );

        $names = array_map(
            static fn (State $state): string => $state->name,
            $definition->allowedTransitions(new State('a')),
        );

        $this->assertSame(['b', 'c'], $names);
    }

    public function test_it_rejects_an_empty_state_list(): void
    {
        $this->expectException(InvalidWorkflowDefinitionException::class);

        ArrayWorkflowDefinition::fromArray([], []);
    }

    public function test_it_rejects_a_transition_from_an_unknown_state(): void
    {
        $this->expectException(InvalidWorkflowDefinitionException::class);

        ArrayWorkflowDefinition::fromArray(['draft'], ['pending' => ['draft']]);
    }

    public function test_it_rejects_a_transition_to_an_unknown_state(): void
    {
        $this->expectException(InvalidWorkflowDefinitionException::class);

        ArrayWorkflowDefinition::fromArray(['draft'], ['draft' => ['paid']]);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Domain\Workflow;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Domain\Workflow\ArrayWorkflowDefinition;
use Yammi\Workflow\Domain\Workflow\Exception\IllegalTransitionException;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class StateMachineTest extends TestCase
{
    private function machine(): StateMachine
    {
        return new StateMachine(ArrayWorkflowDefinition::fromArray(
            ['draft', 'pending', 'approved', 'rejected'],
            [
                'draft' => ['pending'],
                'pending' => ['approved', 'rejected'],
            ],
        ));
    }

    public function test_can_reports_allowed_transitions(): void
    {
        $this->assertTrue($this->machine()->can(new State('draft'), new State('pending')));
        $this->assertFalse($this->machine()->can(new State('approved'), new State('draft')));
        $this->assertFalse($this->machine()->can(new State('draft'), new State('paid')));
    }

    public function test_it_returns_the_target_state_for_an_allowed_transition(): void
    {
        $next = $this->machine()->transition(new State('pending'), new State('approved'));

        $this->assertSame('approved', $next->name);
    }

    public function test_it_rejects_an_undeclared_transition(): void
    {
        $this->expectException(IllegalTransitionException::class);

        $this->machine()->transition(new State('approved'), new State('draft'));
    }

    public function test_it_rejects_a_transition_from_an_unknown_state(): void
    {
        $this->expectException(IllegalTransitionException::class);

        $this->machine()->transition(new State('paid'), new State('draft'));
    }

    public function test_it_rejects_a_transition_to_an_unknown_state(): void
    {
        $this->expectException(IllegalTransitionException::class);

        $this->machine()->transition(new State('draft'), new State('paid'));
    }

    public function test_it_delegates_allowed_transitions(): void
    {
        $names = array_map(
            static fn (State $state): string => $state->name,
            $this->machine()->allowedTransitions(new State('pending')),
        );

        $this->assertSame(['approved', 'rejected'], $names);
    }
}

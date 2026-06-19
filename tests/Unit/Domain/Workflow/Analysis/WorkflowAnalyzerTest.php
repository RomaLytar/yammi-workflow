<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Domain\Workflow\Analysis;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Domain\Workflow\Analysis\WorkflowAnalysis;
use Yammi\Workflow\Domain\Workflow\Analysis\WorkflowAnalyzer;
use Yammi\Workflow\Domain\Workflow\ArrayWorkflowDefinition;

final class WorkflowAnalyzerTest extends TestCase
{
    /**
     * @param  list<string>  $states
     * @param  array<string, list<string>>  $transitions
     */
    private function analyze(array $states, array $transitions, string $initial = 'draft'): WorkflowAnalysis
    {
        return (new WorkflowAnalyzer)->analyze(ArrayWorkflowDefinition::fromArray($states, $transitions, $initial));
    }

    public function test_a_linear_workflow_is_healthy(): void
    {
        $analysis = $this->analyze(['draft', 'pending', 'approved'], ['draft' => ['pending'], 'pending' => ['approved']]);

        $this->assertTrue($analysis->isHealthy());
        $this->assertSame([], $analysis->unreachable);
        $this->assertTrue($analysis->hasTerminalState);
        $this->assertSame([], $analysis->messages());
    }

    public function test_it_finds_unreachable_states(): void
    {
        $analysis = $this->analyze(['draft', 'pending', 'orphan'], ['draft' => ['pending']]);

        $this->assertSame(['orphan'], $analysis->unreachable);
        $this->assertFalse($analysis->isHealthy());
        $this->assertContains('State "orphan" is unreachable from the initial state.', $analysis->messages());
    }

    public function test_it_flags_a_missing_terminal_state(): void
    {
        $analysis = $this->analyze(['a', 'b'], ['a' => ['b'], 'b' => ['a']], 'a');

        $this->assertFalse($analysis->hasTerminalState);
        $this->assertFalse($analysis->isHealthy());
        $this->assertContains('The workflow has no terminal state — every state can move on, so it never ends.', $analysis->messages());
    }
}

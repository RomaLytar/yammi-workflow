<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Definition;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Exception\InvalidWorkflowDefinitionException;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\TestCase;

final class WorkflowImporterTest extends TestCase
{
    use RefreshDatabase;

    private function importInvoice(): void
    {
        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'name' => 'Invoice approval',
            'states' => ['draft', 'pending', 'approved', 'rejected'],
            'initial' => 'draft',
            'transitions' => [
                'draft' => ['pending'],
                'pending' => ['approved', 'rejected'],
            ],
        ]));
    }

    public function test_it_imports_a_definition_readable_through_the_repository(): void
    {
        $this->importInvoice();

        $definition = $this->repository()->find('invoice');

        $this->assertTrue($definition->allows(new State('draft'), new State('pending')));
        $this->assertTrue($definition->allows(new State('pending'), new State('approved')));
        $this->assertFalse($definition->allows(new State('approved'), new State('draft')));
    }

    public function test_it_persists_states_and_edges_as_normalized_rows(): void
    {
        $this->importInvoice();

        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'name' => 'Invoice approval']);
        $this->assertDatabaseHas('workflow_states', ['key' => 'draft', 'is_initial' => true]);
        $this->assertDatabaseHas('workflow_states', ['key' => 'pending', 'is_initial' => false]);
        $this->assertDatabaseCount('workflow_transition_defs', 3);
    }

    public function test_find_throws_for_an_unknown_workflow(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $this->repository()->find('missing');
    }

    public function test_it_rejects_an_initial_state_outside_the_declared_set(): void
    {
        $this->expectException(InvalidWorkflowDefinitionException::class);

        $this->app->make(WorkflowImporter::class)->import(
            new WorkflowBlueprintData('orphan', 'Orphan', ['draft'], [], 'pending'),
        );
    }

    private function repository(): WorkflowDefinitionRepository
    {
        return $this->app->make(WorkflowDefinitionRepository::class);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Designer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Designer\GraphToBlueprint;
use Yammi\Workflow\Tests\TestCase;

final class GraphToBlueprintTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{key: string, name: string, nodes: list<array{id: string, key: string, initial?: bool}>, edges: list<array{from: string, to: string}>}
     */
    private function graph(): array
    {
        return [
            'key' => 'invoice',
            'name' => 'Invoice approval',
            'nodes' => [
                ['id' => 'n1', 'key' => 'draft', 'initial' => true],
                ['id' => 'n2', 'key' => 'pending'],
                ['id' => 'n3', 'key' => 'approved'],
            ],
            'edges' => [
                ['from' => 'n1', 'to' => 'n2'],
                ['from' => 'n2', 'to' => 'n3'],
            ],
        ];
    }

    public function test_it_converts_a_graph_into_a_blueprint(): void
    {
        $blueprint = GraphToBlueprint::convert($this->graph());

        $this->assertSame('invoice', $blueprint->key);
        $this->assertSame('Invoice approval', $blueprint->name);
        $this->assertSame(['draft', 'pending', 'approved'], $blueprint->states);
        $this->assertSame('draft', $blueprint->initial);
        $this->assertSame(['draft' => ['pending'], 'pending' => ['approved']], $blueprint->transitions);
    }

    public function test_a_converted_graph_imports_into_a_usable_definition(): void
    {
        $this->app->make(WorkflowImporter::class)->import(GraphToBlueprint::convert($this->graph()));

        $definition = $this->app->make(WorkflowDefinitionRepository::class)->find('invoice');

        $this->assertTrue($definition->allows(new State('draft'), new State('pending')));
        $this->assertTrue($definition->allows(new State('pending'), new State('approved')));
        $this->assertSame('draft', $definition->initialState()->name);
    }
}

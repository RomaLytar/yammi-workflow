<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Diagram;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Facade\Workflow;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\TestCase;

final class WorkflowDiagramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'states' => ['draft', 'pending', 'approved', 'rejected'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending'], 'pending' => ['approved', 'rejected']],
        ]));
    }

    public function test_it_renders_a_mermaid_state_diagram(): void
    {
        $diagram = Workflow::diagram('invoice');

        $this->assertStringContainsString('stateDiagram-v2', $diagram);
        $this->assertStringContainsString('[*] --> draft', $diagram);
        $this->assertStringContainsString('draft --> pending', $diagram);
        $this->assertStringContainsString('pending --> approved', $diagram);
        $this->assertStringContainsString('pending --> rejected', $diagram);
    }

    public function test_the_command_prints_a_diagram(): void
    {
        $this->artisan('workflow:diagram', ['key' => 'invoice'])->assertSuccessful();
        $this->artisan('workflow:diagram', ['key' => 'ghost'])->assertFailed();
    }
}

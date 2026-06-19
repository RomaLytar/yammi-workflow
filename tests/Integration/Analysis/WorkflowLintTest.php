<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Analysis;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Facade\Workflow;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\TestCase;

final class WorkflowLintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'states' => ['draft', 'pending', 'approved'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending'], 'pending' => ['approved']],
        ]));
    }

    public function test_the_facade_reports_a_healthy_workflow(): void
    {
        $this->assertTrue(Workflow::analyze('invoice')->isHealthy());
        $this->artisan('workflow:lint', ['key' => 'invoice'])->assertSuccessful();
    }

    public function test_the_command_fails_for_an_unreachable_state(): void
    {
        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'broken',
            'states' => ['draft', 'pending', 'orphan'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending']],
        ]));

        $this->artisan('workflow:lint', ['key' => 'broken'])->assertFailed();
    }

    public function test_the_command_fails_for_an_unknown_workflow(): void
    {
        $this->artisan('workflow:lint', ['key' => 'ghost'])->assertFailed();
    }
}

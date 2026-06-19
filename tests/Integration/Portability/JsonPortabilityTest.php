<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Portability;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Designer\BlueprintExporter;
use Yammi\Workflow\Tests\TestCase;

final class JsonPortabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'name' => 'Invoice approval',
            'states' => ['draft', 'pending', 'approved'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending'], 'pending' => ['approved']],
            'conditions' => ['draft>pending' => [['field' => 'amount', 'operator' => 'gte', 'value' => '1000']]],
            'actions' => ['pending>approved' => ['notify']],
            'approval' => ['pending>approved' => ['manager', 'finance']],
        ]));
    }

    public function test_the_exporter_produces_an_importable_spec(): void
    {
        $spec = $this->app->make(BlueprintExporter::class)->export('invoice');

        $this->assertSame('invoice', $spec['key']);
        $this->assertSame(['draft', 'pending', 'approved'], $spec['states']);
        $this->assertSame('draft', $spec['initial']);
        $this->assertSame(['draft' => ['pending'], 'pending' => ['approved']], $spec['transitions']);
        $this->assertSame(['draft>pending' => [['field' => 'amount', 'operator' => 'gte', 'value' => '1000']]], $spec['conditions']);
        $this->assertSame(['pending>approved' => ['notify']], $spec['actions']);
        $this->assertSame(['pending>approved' => ['manager', 'finance']], $spec['approval']);
    }

    public function test_export_then_import_round_trips_into_a_new_version(): void
    {
        $spec = $this->app->make(BlueprintExporter::class)->export('invoice');

        $path = (string) tempnam(sys_get_temp_dir(), 'wf');
        file_put_contents($path, (string) json_encode($spec));

        $this->artisan('workflow:import', ['file' => $path])->assertSuccessful();
        @unlink($path);

        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'version' => 2, 'is_current' => true]);
    }

    public function test_export_command_runs_and_reports_unknown_keys(): void
    {
        $this->artisan('workflow:export', ['key' => 'invoice'])->assertSuccessful();
        $this->artisan('workflow:export', ['key' => 'ghost'])->assertFailed();
    }

    public function test_import_of_a_missing_file_fails(): void
    {
        $this->artisan('workflow:import', ['file' => '/does/not/exist.json'])->assertFailed();
    }
}

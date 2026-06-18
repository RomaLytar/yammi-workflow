<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Metrics;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Facade\Workflow;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class MetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('workflow.subjects', [Invoice::class => 'invoice']);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow_state')->nullable();
        });
    }

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

    public function test_it_reports_the_instance_count_per_state(): void
    {
        Invoice::create()->transitionTo('pending');
        Invoice::create()->transitionTo('pending');

        $approved = Invoice::create();
        $approved->transitionTo('pending');
        $approved->transitionTo('approved');

        $this->assertSame(
            ['draft' => 0, 'pending' => 2, 'approved' => 1, 'rejected' => 0],
            Workflow::metrics('invoice'),
        );
    }
}

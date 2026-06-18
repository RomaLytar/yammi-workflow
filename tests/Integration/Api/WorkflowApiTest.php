<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Facade\Workflow;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class WorkflowApiTest extends TestCase
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
            'transitions' => [
                'draft' => ['pending'],
                'pending' => ['approved', 'rejected'],
            ],
        ]));
    }

    public function test_the_facade_returns_a_full_snapshot(): void
    {
        $invoice = Invoice::create();
        Workflow::transition($invoice, 'pending');

        $snapshot = Workflow::for($invoice);

        $this->assertSame('invoice', $snapshot->workflow);
        $this->assertSame('pending', $snapshot->current);
        $this->assertSame(['approved', 'rejected'], $snapshot->allowed);
        $this->assertCount(1, $snapshot->history);
        $this->assertSame('draft', $snapshot->history[0]->from);
        $this->assertSame('pending', $snapshot->history[0]->to);
        $this->assertNotSame('', $snapshot->history[0]->at);
    }

    public function test_the_facade_exposes_the_read_helpers(): void
    {
        $invoice = Invoice::create();

        $this->assertSame('draft', Workflow::currentState($invoice));
        $this->assertSame(['pending'], Workflow::allowedTransitions($invoice));
        $this->assertSame([], Workflow::history($invoice));

        Workflow::transition($invoice, 'pending');

        $this->assertSame('pending', Workflow::currentState($invoice));
        $this->assertCount(1, Workflow::history($invoice));
    }
}

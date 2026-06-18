<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\History;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Events\WorkflowTransitioned;
use Yammi\Workflow\Facade\Workflow;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class TransitionReasonTest extends TestCase
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
            'states' => ['draft', 'pending', 'approved'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending'], 'pending' => ['approved']],
        ]));
    }

    public function test_it_records_a_reason_and_metadata(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending', 'looks legit', ['ip' => '127.0.0.1', 'amount' => 1200]);

        $this->assertDatabaseHas('workflow_transitions', ['reason' => 'looks legit']);

        $history = Workflow::history($invoice);

        $this->assertCount(1, $history);
        $this->assertSame('looks legit', $history[0]->reason);
        $this->assertSame(['ip' => '127.0.0.1', 'amount' => 1200], $history[0]->meta);
    }

    public function test_the_event_carries_the_reason_and_metadata(): void
    {
        Event::fake([WorkflowTransitioned::class]);

        Invoice::create()->transitionTo('pending', 'because', ['k' => 'v']);

        Event::assertDispatched(
            WorkflowTransitioned::class,
            static fn (WorkflowTransitioned $event): bool => $event->reason === 'because' && $event->meta === ['k' => 'v'],
        );
    }
}

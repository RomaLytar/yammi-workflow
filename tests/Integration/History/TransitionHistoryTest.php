<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\History;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Events\WorkflowTransitioned;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class TransitionHistoryTest extends TestCase
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

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
    }

    private function importInvoiceWorkflow(): void
    {
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

    public function test_it_records_each_transition_in_history(): void
    {
        $this->importInvoiceWorkflow();
        $invoice = Invoice::create();

        $invoice->transitionTo('pending');
        $invoice->transitionTo('approved');

        $this->assertDatabaseCount('workflow_transitions', 2);
        $this->assertDatabaseHas('workflow_transitions', [
            'subject_type' => $invoice->getMorphClass(),
            'subject_id' => (string) $invoice->id,
            'actor_type' => null,
        ]);
    }

    public function test_it_dispatches_a_domain_event_per_transition(): void
    {
        $this->importInvoiceWorkflow();
        Event::fake([WorkflowTransitioned::class]);

        Invoice::create()->transitionTo('pending');

        Event::assertDispatched(
            WorkflowTransitioned::class,
            static fn (WorkflowTransitioned $event): bool => $event->workflow === 'invoice'
                && $event->from === 'draft'
                && $event->to === 'pending',
        );
    }

    public function test_it_captures_the_authenticated_actor(): void
    {
        $this->importInvoiceWorkflow();
        $user = User::create(['name' => 'Manager']);

        $this->actingAs($user);
        Invoice::create()->transitionTo('pending');

        $this->assertDatabaseHas('workflow_transitions', [
            'actor_type' => $user::class,
            'actor_id' => (string) $user->id,
        ]);
    }
}

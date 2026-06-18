<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Runtime;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use stdClass;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Exception\IllegalTransitionException;
use Yammi\Workflow\Domain\Workflow\Exception\SubjectNotMappedException;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class HasWorkflowTest extends TestCase
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

    public function test_a_fresh_subject_reports_the_initial_state(): void
    {
        $this->importInvoiceWorkflow();

        $invoice = Invoice::create();

        $this->assertSame('draft', $invoice->currentState());
        $this->assertSame(['pending'], $invoice->allowedTransitions());
    }

    public function test_it_transitions_through_declared_states_with_the_instance_store(): void
    {
        $this->importInvoiceWorkflow();
        $invoice = Invoice::create();

        $result = $invoice->transitionTo('pending');

        $this->assertSame('draft', $result->from);
        $this->assertSame('pending', $result->to);
        $this->assertSame('pending', $invoice->currentState());

        $invoice->transitionTo('approved');
        $this->assertSame('approved', $invoice->currentState());

        $this->assertDatabaseCount('workflow_instances', 1);
    }

    public function test_it_rejects_an_illegal_transition_and_keeps_the_state(): void
    {
        $this->importInvoiceWorkflow();
        $invoice = Invoice::create();

        try {
            $invoice->transitionTo('approved');
            $this->fail('Expected an IllegalTransitionException.');
        } catch (IllegalTransitionException) {
            // expected
        }

        $this->assertSame('draft', $invoice->currentState());
    }

    public function test_the_column_store_keeps_the_state_on_the_model(): void
    {
        config()->set('workflow.state_store.driver', 'column');

        $this->importInvoiceWorkflow();
        $invoice = Invoice::create();

        $invoice->transitionTo('pending');

        $this->assertSame('pending', $invoice->workflow_state);
        $this->assertSame('pending', $invoice->currentState());
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'workflow_state' => 'pending']);
        $this->assertDatabaseCount('workflow_instances', 0);
    }

    public function test_an_unmapped_subject_is_rejected(): void
    {
        $this->expectException(SubjectNotMappedException::class);

        $this->app->make(WorkflowKeyResolver::class)->keyFor(new stdClass);
    }
}

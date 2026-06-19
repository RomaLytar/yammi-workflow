<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Approval;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Approval\Exception\ApprovalRequiredException;
use Yammi\Workflow\Facade\Approval;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class ApprovalGateTest extends TestCase
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
            'approval' => ['pending>approved' => ['manager', 'finance']],
        ]));
    }

    public function test_a_gated_transition_requests_approval_and_blocks(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        try {
            $invoice->transitionTo('approved');
            $this->fail('Expected an ApprovalRequiredException.');
        } catch (ApprovalRequiredException) {
            // expected
        }

        $summary = Approval::for($invoice);

        $this->assertCount(2, $summary->steps);
        $this->assertSame(1, $summary->currentStep);
        $this->assertSame('pending', $invoice->currentState());
    }

    public function test_the_transition_passes_after_full_sign_off(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        try {
            $invoice->transitionTo('approved');
        } catch (ApprovalRequiredException) {
            // the first attempt opens the approval flow
        }

        Approval::approve($invoice);
        Approval::approve($invoice);

        $invoice->transitionTo('approved');

        $this->assertSame('approved', $invoice->currentState());
    }
}

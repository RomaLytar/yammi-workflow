<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Guard;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Exception\TransitionBlockedException;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Guard\InMemoryGuardRegistry;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class GuardTest extends TestCase
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

        $this->app->make(InMemoryGuardRegistry::class)->registerUsing(
            'invoice',
            static fn (object $subject, string $from, string $to): bool => ! ($from === 'pending' && $to === 'approved'),
        );
    }

    public function test_a_guard_hides_a_blocked_transition_from_the_allowed_list(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->assertSame(['rejected'], $invoice->allowedTransitions());
    }

    public function test_a_guard_blocks_the_transition(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->expectException(TransitionBlockedException::class);

        $invoice->transitionTo('approved');
    }

    public function test_a_guard_allows_the_unblocked_transition(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');
        $invoice->transitionTo('rejected');

        $this->assertSame('rejected', $invoice->currentState());
    }
}

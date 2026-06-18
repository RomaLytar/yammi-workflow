<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Timer;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Guard\InMemoryGuardRegistry;
use Yammi\Workflow\Infrastructure\Timer\TimerRegistry;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class TimerTest extends TestCase
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

        $this->app->make(TimerRegistry::class)->after('invoice', 'pending', 3600, 'rejected');
    }

    public function test_it_auto_transitions_an_overdue_instance(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->travel(2)->hours();
        $this->artisan('workflow:tick')->assertSuccessful();
        $this->travelBack();

        $this->assertSame('rejected', $invoice->currentState());
    }

    public function test_it_leaves_a_fresh_instance_alone(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->artisan('workflow:tick')->assertSuccessful();

        $this->assertSame('pending', $invoice->currentState());
    }

    public function test_it_skips_unknown_timers_and_guard_blocked_moves(): void
    {
        $this->app->make(TimerRegistry::class)->after('ghost-workflow', 'pending', 1, 'x');
        $this->app->make(TimerRegistry::class)->after('invoice', 'ghost-state', 1, 'x');
        $this->app->make(InMemoryGuardRegistry::class)->registerUsing(
            'invoice',
            static fn (object $subject, string $from, string $to): bool => ! ($from === 'pending' && $to === 'rejected'),
        );

        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->travel(2)->hours();
        $this->artisan('workflow:tick')->assertSuccessful();
        $this->travelBack();

        $this->assertSame('pending', $invoice->currentState());
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Hook;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Hook\InMemoryHookRegistry;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class HookTest extends TestCase
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

    public function test_it_runs_leave_transition_and_enter_hooks_in_order(): void
    {
        $calls = [];
        $registry = $this->app->make(InMemoryHookRegistry::class);
        $registry->onLeave('invoice', 'draft', function () use (&$calls): void {
            $calls[] = 'leave:draft';
        });
        $registry->onTransition('invoice', function (object $subject, string $from, string $to) use (&$calls): void {
            $calls[] = "transition:{$from}>{$to}";
        });
        $registry->onEnter('invoice', 'pending', function () use (&$calls): void {
            $calls[] = 'enter:pending';
        });
        $registry->onEnter('invoice', 'approved', function () use (&$calls): void {
            $calls[] = 'enter:approved';
        });

        Invoice::create()->transitionTo('pending');

        $this->assertSame(['leave:draft', 'transition:draft>pending', 'enter:pending'], $calls);
    }

    public function test_it_passes_the_subject_to_the_hook(): void
    {
        $seen = null;
        $invoice = Invoice::create();

        $this->app->make(InMemoryHookRegistry::class)->onEnter('invoice', 'pending', function (object $subject) use (&$seen): void {
            $seen = $subject;
        });

        $invoice->transitionTo('pending');

        $this->assertSame($invoice, $seen);
    }
}

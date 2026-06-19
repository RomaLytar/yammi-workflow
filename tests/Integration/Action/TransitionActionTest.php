<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Action;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Action\InMemoryActionCatalog;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class TransitionActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<int> */
    private array $ran = [];

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
            'actions' => ['draft>pending' => ['mark']],
        ]));

        $this->app->make(InMemoryActionCatalog::class)->register('mark', function (object $subject): void {
            $this->ran[] = $subject->getKey();
        });
    }

    public function test_a_wired_action_runs_on_the_transition(): void
    {
        $invoice = Invoice::create();

        $invoice->transitionTo('pending');

        $this->assertSame([$invoice->id], $this->ran);
    }

    public function test_a_transition_without_a_wired_action_runs_nothing(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');
        $this->ran = [];

        $invoice->transitionTo('approved');

        $this->assertSame([], $this->ran);
    }
}

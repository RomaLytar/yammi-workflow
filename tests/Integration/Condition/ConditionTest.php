<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Condition;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Exception\TransitionBlockedException;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class ConditionTest extends TestCase
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
            $table->unsignedInteger('amount')->default(0);
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
            'conditions' => [
                'draft>pending' => [['field' => 'amount', 'operator' => 'gte', 'value' => '1000']],
            ],
        ]));
    }

    public function test_a_condition_blocks_and_hides_a_transition(): void
    {
        $invoice = Invoice::create(['amount' => 500]);

        $this->assertSame([], $invoice->allowedTransitions());

        $this->expectException(TransitionBlockedException::class);
        $invoice->transitionTo('pending');
    }

    public function test_a_satisfied_condition_allows_the_transition(): void
    {
        $invoice = Invoice::create(['amount' => 2000]);

        $this->assertSame(['pending'], $invoice->allowedTransitions());

        $invoice->transitionTo('pending');

        $this->assertSame('pending', $invoice->currentState());
    }
}

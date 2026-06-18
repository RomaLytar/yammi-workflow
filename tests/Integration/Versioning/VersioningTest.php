<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Versioning;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class VersioningTest extends TestCase
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

    /**
     * @param  list<string>  $states
     * @param  array<string, list<string>>  $transitions
     */
    private function import(array $states, array $transitions): void
    {
        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'states' => $states,
            'initial' => 'draft',
            'transitions' => $transitions,
        ]));
    }

    public function test_import_creates_a_new_version_and_flips_current(): void
    {
        $this->import(['draft', 'pending'], ['draft' => ['pending']]);
        $this->import(['draft', 'pending'], ['draft' => ['pending']]);

        $this->assertDatabaseCount('workflows', 2);
        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'version' => 1, 'is_current' => false]);
        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'version' => 2, 'is_current' => true]);
    }

    public function test_in_flight_instances_stay_pinned_to_their_version(): void
    {
        $this->import(['draft', 'pending', 'approved'], ['draft' => ['pending'], 'pending' => ['approved']]);
        $old = Invoice::create();
        $old->transitionTo('pending');

        $this->import(['draft', 'pending', 'paid'], ['draft' => ['pending'], 'pending' => ['paid']]);
        $new = Invoice::create();
        $new->transitionTo('pending');

        $this->assertSame(['approved'], $old->allowedTransitions());
        $old->transitionTo('approved');
        $this->assertSame('approved', $old->currentState());

        $this->assertSame(['paid'], $new->allowedTransitions());
        $new->transitionTo('paid');
        $this->assertSame('paid', $new->currentState());
    }
}

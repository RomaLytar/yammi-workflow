<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Console;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionModel;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\TestCase;

final class OpsCommandsTest extends TestCase
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
    }

    public function test_list_command_runs(): void
    {
        $this->artisan('workflow:list')->assertSuccessful();
    }

    public function test_show_command_renders_an_existing_workflow(): void
    {
        $this->artisan('workflow:show', ['key' => 'invoice'])->assertSuccessful();
    }

    public function test_show_command_fails_for_an_unknown_workflow(): void
    {
        $this->artisan('workflow:show', ['key' => 'ghost'])->assertFailed();
    }

    public function test_prune_deletes_old_history_only(): void
    {
        $invoice = Invoice::create();
        $invoice->transitionTo('pending');
        $invoice->transitionTo('approved');

        $oldest = WorkflowTransitionModel::query()->orderBy('id')->firstOrFail();
        WorkflowTransitionModel::query()->whereKey($oldest->id)->update([
            'created_at' => now()->subDays(120),
        ]);

        $this->artisan('workflow:prune', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseCount('workflow_transitions', 1);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Authorization;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Exception\UnauthorizedTransitionException;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class AuthorizationTest extends TestCase
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

    public function test_the_gate_hides_and_blocks_an_unauthorized_transition(): void
    {
        Gate::define('workflow.transition', static fn ($user, object $subject, string $from, string $to): bool => $to !== 'approved');

        $this->actingAs(User::create(['name' => 'Manager']));

        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->assertSame(['rejected'], $invoice->allowedTransitions());

        $this->expectException(UnauthorizedTransitionException::class);
        $invoice->transitionTo('approved');
    }

    public function test_a_transition_without_an_authenticated_user_bypasses_the_gate(): void
    {
        Gate::define('workflow.transition', static fn (): bool => false);

        $invoice = Invoice::create();
        $invoice->transitionTo('pending');

        $this->assertSame('pending', $invoice->currentState());
    }
}

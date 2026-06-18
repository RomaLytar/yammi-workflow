<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Presentation;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use stdClass;
use Yammi\Workflow\Infrastructure\Presentation\SubjectPresenter;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class SubjectPresenterTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_title_uses_the_workflow_subject_contract(): void
    {
        $invoice = Invoice::create();

        $this->assertSame(
            sprintf('INV-%03d', $invoice->id),
            SubjectPresenter::title(Invoice::class, (string) $invoice->id),
        );
    }

    public function test_title_falls_back_to_class_and_id(): void
    {
        $user = User::create(['name' => 'Bob']);

        $this->assertSame('User #'.$user->id, SubjectPresenter::title(User::class, (string) $user->id));
    }

    public function test_resolve_finds_the_model(): void
    {
        $invoice = Invoice::create();

        $this->assertTrue(SubjectPresenter::resolve(Invoice::class, (string) $invoice->id)?->is($invoice));
    }

    public function test_resolve_returns_null_for_an_unknown_or_non_model_class(): void
    {
        $this->assertNull(SubjectPresenter::resolve('App\\Models\\Nope', '1'));
        $this->assertNull(SubjectPresenter::resolve(stdClass::class, '1'));
    }
}

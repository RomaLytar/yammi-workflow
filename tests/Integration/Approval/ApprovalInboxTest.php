<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Approval;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Facade\Approval;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class ApprovalInboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
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

    public function test_an_assigned_user_sees_only_their_current_step(): void
    {
        $manager = User::create(['name' => 'Manager']);
        $finance = User::create(['name' => 'Finance']);
        $invoice = Invoice::create();

        Approval::request($invoice, ['manager' => $manager, 'finance' => $finance]);

        $managerInbox = Approval::pendingFor($manager);
        $this->assertCount(1, $managerInbox);
        $this->assertSame('manager', $managerInbox[0]->label);
        $this->assertSame(User::class, $managerInbox[0]->assigneeType);

        $this->assertCount(0, Approval::pendingFor($finance));
    }

    public function test_the_inbox_advances_as_steps_are_approved(): void
    {
        $manager = User::create(['name' => 'Manager']);
        $finance = User::create(['name' => 'Finance']);
        $invoice = Invoice::create();

        Approval::request($invoice, ['manager' => $manager, 'finance' => $finance]);
        Approval::approve($invoice);

        $this->assertCount(0, Approval::pendingFor($manager));

        $financeInbox = Approval::pendingFor($finance);
        $this->assertCount(1, $financeInbox);
        $this->assertSame('finance', $financeInbox[0]->label);
    }
}

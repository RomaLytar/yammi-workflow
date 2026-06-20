<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Approval;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Facade\Approval;
use Yammi\Workflow\Infrastructure\Notification\ApprovalAssignedNotification;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class ApprovalNotificationTest extends TestCase
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

    public function test_the_assignee_is_notified_when_their_step_becomes_current(): void
    {
        Notification::fake();

        $manager = User::create(['name' => 'Manager']);
        $invoice = Invoice::create();

        Approval::request($invoice, ['manager' => $manager]);

        Notification::assertSentTo(
            $manager,
            ApprovalAssignedNotification::class,
            static function (ApprovalAssignedNotification $notification) use ($manager): bool {
                $data = $notification->toArray($manager);
                $mail = $notification->toMail($manager);

                return $notification->via($manager) === ['database']
                    && $data['step'] === 1
                    && $data['label'] === 'manager'
                    && str_contains((string) $mail->subject, 'approval');
            },
        );
    }

    public function test_the_next_assignee_is_notified_after_an_approval(): void
    {
        Notification::fake();

        $manager = User::create(['name' => 'Manager']);
        $finance = User::create(['name' => 'Finance']);
        $invoice = Invoice::create();

        Approval::request($invoice, ['manager' => $manager, 'finance' => $finance]);
        Approval::approve($invoice);

        Notification::assertSentTo($finance, ApprovalAssignedNotification::class);
    }
}

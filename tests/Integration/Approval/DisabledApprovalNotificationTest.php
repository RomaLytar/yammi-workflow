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

final class DisabledApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('workflow.notifications.enabled', false);
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

    public function test_no_notification_is_sent_when_disabled(): void
    {
        Notification::fake();

        $manager = User::create(['name' => 'Manager']);
        $invoice = Invoice::create();

        Approval::request($invoice, ['manager' => $manager]);

        Notification::assertNothingSent();
    }
}

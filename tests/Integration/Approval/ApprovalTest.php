<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Approval;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;
use Yammi\Workflow\Domain\Approval\Exception\NoPendingApprovalException;
use Yammi\Workflow\Facade\Approval;
use Yammi\Workflow\Tests\Support\Invoice;
use Yammi\Workflow\Tests\Support\User;
use Yammi\Workflow\Tests\TestCase;

final class ApprovalTest extends TestCase
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

    public function test_request_creates_ordered_pending_steps(): void
    {
        $invoice = Invoice::create();
        Approval::request($invoice, ['manager', 'finance', 'ceo']);

        $summary = Approval::for($invoice);

        $this->assertCount(3, $summary->steps);
        $this->assertSame(1, $summary->currentStep);
        $this->assertFalse($summary->isApproved);
        $this->assertFalse($summary->isRejected);
        $this->assertSame('manager', $summary->steps[0]->label);
        $this->assertTrue($summary->steps[0]->status->isPending());
    }

    public function test_sequential_approval_completes_the_flow(): void
    {
        $invoice = Invoice::create();
        Approval::request($invoice, ['manager', 'finance', 'ceo']);

        $this->assertSame(2, Approval::approve($invoice)->currentStep);
        $this->assertSame(3, Approval::approve($invoice)->currentStep);

        $final = Approval::approve($invoice);

        $this->assertNull($final->currentStep);
        $this->assertTrue($final->isApproved);
        $this->assertFalse($final->isRejected);
    }

    public function test_a_rejection_halts_the_flow(): void
    {
        $invoice = Invoice::create();
        Approval::request($invoice, ['manager', 'finance']);
        Approval::approve($invoice);

        $summary = Approval::reject($invoice, 'budget exceeded');

        $this->assertTrue($summary->isRejected);
        $this->assertFalse($summary->isApproved);
        $this->assertNull($summary->currentStep);
    }

    public function test_it_records_the_actor_and_comment(): void
    {
        $invoice = Invoice::create();
        Approval::request($invoice, ['manager']);

        $this->actingAs(User::create(['name' => 'Boss']));
        $summary = Approval::approve($invoice, 'looks good');

        $this->assertSame(ApprovalStatus::Approved, $summary->steps[0]->status);
        $this->assertSame('looks good', $summary->steps[0]->comment);
        $this->assertSame(User::class, $summary->steps[0]->decidedByType);
    }

    public function test_approving_with_nothing_pending_throws(): void
    {
        $invoice = Invoice::create();
        Approval::request($invoice, ['manager']);
        Approval::approve($invoice);

        $this->expectException(NoPendingApprovalException::class);
        Approval::approve($invoice);
    }
}

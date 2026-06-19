<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->string('assignee_type')->nullable()->after('label');
            $table->string('assignee_id')->nullable()->after('assignee_type');

            $table->index(['assignee_type', 'assignee_id']);
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->dropColumn(['assignee_type', 'assignee_id']);
        });
    }

    private function table(): string
    {
        return (string) config('workflow.tables.approvals', 'workflow_approvals');
    }
};

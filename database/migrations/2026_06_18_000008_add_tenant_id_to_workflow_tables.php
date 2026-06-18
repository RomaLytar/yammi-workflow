<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('tenant_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('tenant_id');
            });
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return [
            (string) config('workflow.tables.workflows', 'workflows'),
            (string) config('workflow.tables.instances', 'workflow_instances'),
            (string) config('workflow.tables.transitions', 'workflow_transitions'),
            (string) config('workflow.tables.approvals', 'workflow_approvals'),
        ];
    }
};

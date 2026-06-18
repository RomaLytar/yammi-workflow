<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table): void {
            $table->smallIncrements('id');
            $table->unsignedBigInteger('workflow_id');
            $table->string('key');
            $table->string('label');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_initial')->default(false);

            $table->unique(['workflow_id', 'key']);
            $table->foreign('workflow_id')
                ->references('id')->on($this->workflowsTable())
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    private function table(): string
    {
        return (string) config('workflow.tables.states', 'workflow_states');
    }

    private function workflowsTable(): string
    {
        return (string) config('workflow.tables.workflows', 'workflows');
    }
};

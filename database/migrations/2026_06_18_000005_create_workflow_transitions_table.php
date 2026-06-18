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
            $table->id();
            $table->string('subject_type');
            $table->string('subject_id');
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedSmallInteger('from_state_id');
            $table->unsignedSmallInteger('to_state_id');
            $table->string('actor_type')->nullable();
            $table->string('actor_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('workflow_id');

            $table->foreign('from_state_id')
                ->references('id')->on($this->statesTable())
                ->cascadeOnDelete();
            $table->foreign('to_state_id')
                ->references('id')->on($this->statesTable())
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    private function table(): string
    {
        return (string) config('workflow.tables.transitions', 'workflow_transitions');
    }

    private function statesTable(): string
    {
        return (string) config('workflow.tables.states', 'workflow_states');
    }
};

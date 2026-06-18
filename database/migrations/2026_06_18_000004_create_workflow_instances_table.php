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
            $table->unsignedSmallInteger('state_id');
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id']);
            $table->foreign('state_id')
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
        return (string) config('workflow.tables.instances', 'workflow_instances');
    }

    private function statesTable(): string
    {
        return (string) config('workflow.tables.states', 'workflow_states');
    }
};

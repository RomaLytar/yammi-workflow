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
            $table->unsignedSmallInteger('step');
            $table->string('label');
            $table->unsignedTinyInteger('status')->default(0);
            $table->string('decided_by_type')->nullable();
            $table->string('decided_by_id')->nullable();
            $table->string('comment')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->unique(['subject_type', 'subject_id', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    private function table(): string
    {
        return (string) config('workflow.tables.approvals', 'workflow_approvals');
    }
};

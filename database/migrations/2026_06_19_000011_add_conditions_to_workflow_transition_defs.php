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
            $table->json('conditions')->nullable()->after('to_state_id');
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->dropColumn('conditions');
        });
    }

    private function table(): string
    {
        return (string) config('workflow.tables.transition_defs', 'workflow_transition_defs');
    }
};

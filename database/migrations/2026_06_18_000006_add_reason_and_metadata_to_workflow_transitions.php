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
            $table->string('reason')->nullable()->after('actor_id');
            $table->json('meta')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->dropColumn(['reason', 'meta']);
        });
    }

    private function table(): string
    {
        return (string) config('workflow.tables.transitions', 'workflow_transitions');
    }
};

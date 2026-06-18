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
            $table->unsignedSmallInteger('version')->default(1)->after('name');
            $table->boolean('is_current')->default(true)->after('version');

            $table->dropUnique(['key']);
            $table->unique(['key', 'version', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->dropUnique(['key', 'version', 'tenant_id']);
            $table->unique(['key']);
            $table->dropColumn(['version', 'is_current']);
        });
    }

    private function table(): string
    {
        return (string) config('workflow.tables.workflows', 'workflows');
    }
};

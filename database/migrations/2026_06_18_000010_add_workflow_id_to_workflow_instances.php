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
            $table->unsignedBigInteger('workflow_id')->nullable()->after('subject_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->dropColumn('workflow_id');
        });
    }

    private function table(): string
    {
        return (string) config('workflow.tables.instances', 'workflow_instances');
    }
};

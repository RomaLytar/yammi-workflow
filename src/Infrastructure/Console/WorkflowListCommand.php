<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

/**
 * @internal
 */
final class WorkflowListCommand extends Command
{
    protected $signature = 'workflow:list';

    protected $description = 'List the workflows defined in the database';

    public function handle(): int
    {
        $rows = WorkflowModel::query()
            ->orderBy('key')
            ->get()
            ->map(static fn (WorkflowModel $workflow): array => [
                $workflow->key,
                $workflow->name,
                (string) $workflow->states()->count(),
            ])
            ->all();

        $this->table(['Key', 'Name', 'States'], $rows);

        return self::SUCCESS;
    }
}

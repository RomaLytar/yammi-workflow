<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionModel;

/**
 * @internal
 */
final class PruneHistoryCommand extends Command
{
    protected $signature = 'workflow:prune {--days=90}';

    protected $description = 'Delete transition history older than the given number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = WorkflowTransitionModel::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} transition record(s) older than {$days} days.");

        return self::SUCCESS;
    }
}

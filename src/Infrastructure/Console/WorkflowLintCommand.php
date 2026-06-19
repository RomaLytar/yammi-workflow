<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Application\Service\WorkflowLinter;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;

/**
 * @internal
 */
final class WorkflowLintCommand extends Command
{
    protected $signature = 'workflow:lint {key}';

    protected $description = 'Check a workflow for unreachable states and a missing terminal state';

    public function __construct(
        private readonly WorkflowLinter $linter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $argument = $this->argument('key');
        $key = is_string($argument) ? $argument : '';

        try {
            $analysis = $this->linter->analyze($key);
        } catch (WorkflowNotFoundException) {
            $this->error("Workflow \"{$key}\" not found.");

            return self::FAILURE;
        }

        if ($analysis->isHealthy()) {
            $this->info("Workflow \"{$key}\" looks healthy.");

            return self::SUCCESS;
        }

        foreach ($analysis->messages() as $message) {
            $this->warn($message);
        }

        return self::FAILURE;
    }
}

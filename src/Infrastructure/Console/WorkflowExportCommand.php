<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;
use Yammi\Workflow\Infrastructure\Designer\BlueprintExporter;

/**
 * @internal
 */
final class WorkflowExportCommand extends Command
{
    protected $signature = 'workflow:export {key} {--pretty}';

    protected $description = 'Export a workflow definition as JSON';

    public function __construct(
        private readonly BlueprintExporter $exporter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $argument = $this->argument('key');
        $key = is_string($argument) ? $argument : '';

        try {
            $spec = $this->exporter->export($key);
        } catch (WorkflowNotFoundException) {
            $this->error("Workflow \"{$key}\" not found.");

            return self::FAILURE;
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | ($this->option('pretty') ? JSON_PRETTY_PRINT : 0);

        $this->line((string) json_encode($spec, $flags));

        return self::SUCCESS;
    }
}

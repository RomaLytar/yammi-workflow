<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Application\Service\WorkflowDiagram;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;

/**
 * @internal
 */
final class WorkflowDiagramCommand extends Command
{
    protected $signature = 'workflow:diagram {key}';

    protected $description = 'Print a workflow as a Mermaid state diagram';

    public function __construct(
        private readonly WorkflowDiagram $diagrams,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $argument = $this->argument('key');
        $key = is_string($argument) ? $argument : '';

        try {
            $this->line($this->diagrams->mermaid($key));
        } catch (WorkflowNotFoundException) {
            $this->error("Workflow \"{$key}\" not found.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

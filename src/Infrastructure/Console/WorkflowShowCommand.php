<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

/**
 * @internal
 */
final class WorkflowShowCommand extends Command
{
    protected $signature = 'workflow:show {key}';

    protected $description = 'Show the states and transitions of a workflow';

    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $argument = $this->argument('key');
        $key = is_string($argument) ? $argument : '';

        try {
            $definition = $this->definitions->find($key);
        } catch (WorkflowNotFoundException) {
            $this->error("Workflow \"{$key}\" not found.");

            return self::FAILURE;
        }

        $rows = [];

        foreach ($definition->states() as $state) {
            $targets = array_map(
                static fn (State $target): string => $target->name,
                $definition->allowedTransitions($state),
            );

            $label = $definition->initialState()->equals($state) ? $state->name.' (initial)' : $state->name;
            $rows[] = [$label, $targets === [] ? '-' : implode(', ', $targets)];
        }

        $this->table(['State', 'Transitions to'], $rows);

        return self::SUCCESS;
    }
}

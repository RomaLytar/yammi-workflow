<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Domain\Workflow\Exception\TransitionBlockedException;
use Yammi\Workflow\Domain\Workflow\Exception\UnauthorizedTransitionException;
use Yammi\Workflow\Infrastructure\Api\WorkflowManager;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowInstanceModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Timer\Timer;
use Yammi\Workflow\Infrastructure\Timer\TimerRegistry;

/**
 * @internal
 */
final class TickWorkflowsCommand extends Command
{
    protected $signature = 'workflow:tick';

    protected $description = 'Apply elapsed workflow timers as SLA auto-transitions';

    public function __construct(
        private readonly TimerRegistry $timers,
        private readonly WorkflowManager $workflow,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $moved = 0;

        foreach ($this->timers->all() as $timer) {
            $moved += $this->applyTimer($timer);
        }

        $this->info("Applied {$moved} timer transition(s).");

        return self::SUCCESS;
    }

    private function applyTimer(Timer $timer): int
    {
        $workflow = WorkflowModel::query()->where('key', $timer->workflow)->first();

        if ($workflow === null) {
            return 0;
        }

        $state = WorkflowStateModel::query()
            ->where('workflow_id', $workflow->id)
            ->where('key', $timer->state)
            ->first();

        if ($state === null) {
            return 0;
        }

        $cutoff = now()->subSeconds($timer->seconds);
        $moved = 0;

        $instances = WorkflowInstanceModel::query()
            ->where('state_id', $state->id)
            ->where('updated_at', '<=', $cutoff)
            ->get();

        foreach ($instances as $instance) {
            $subject = $this->subjectOf($instance);

            if ($subject === null) {
                continue;
            }

            try {
                $this->workflow->transition($subject, $timer->target, 'SLA timeout');
                $moved++;
            } catch (TransitionBlockedException|UnauthorizedTransitionException) {
                continue;
            }
        }

        return $moved;
    }

    private function subjectOf(WorkflowInstanceModel $instance): ?object
    {
        $class = $instance->subject_type;

        if (! class_exists($class)) {
            return null;
        }

        $subject = $class::query()->find($instance->subject_id);

        return $subject instanceof Model ? $subject : null;
    }
}

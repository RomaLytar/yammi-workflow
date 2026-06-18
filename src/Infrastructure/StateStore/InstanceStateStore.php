<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\StateStore;

use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowInstanceModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;

/**
 * @internal
 */
final class InstanceStateStore implements StateStore
{
    public function __construct(
        private readonly WorkflowKeyResolver $keys,
    ) {}

    public function current(object $subject): ?State
    {
        $instance = WorkflowInstanceModel::query()
            ->where('subject_type', $this->typeOf($subject))
            ->where('subject_id', $this->idOf($subject))
            ->first();

        if ($instance === null) {
            return null;
        }

        $state = WorkflowStateModel::query()->whereKey($instance->state_id)->first();

        return $state === null ? null : new State($state->key);
    }

    public function put(object $subject, State $state): void
    {
        $workflow = WorkflowModel::query()
            ->where('key', $this->keys->keyFor($subject))
            ->firstOrFail();

        $row = WorkflowStateModel::query()
            ->where('workflow_id', $workflow->id)
            ->where('key', $state->name)
            ->firstOrFail();

        WorkflowInstanceModel::query()->updateOrCreate(
            [
                'subject_type' => $this->typeOf($subject),
                'subject_id' => $this->idOf($subject),
            ],
            ['state_id' => $row->id],
        );
    }

    private function typeOf(object $subject): string
    {
        return $subject instanceof Model ? $subject->getMorphClass() : $subject::class;
    }

    private function idOf(object $subject): string
    {
        return $subject instanceof Model ? (string) $subject->getKey() : '';
    }
}

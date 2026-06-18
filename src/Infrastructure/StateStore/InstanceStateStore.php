<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\StateStore;

use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowInstanceModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Support\SubjectIdentity;

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
            ->where('subject_type', SubjectIdentity::type($subject))
            ->where('subject_id', SubjectIdentity::id($subject))
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
                'subject_type' => SubjectIdentity::type($subject),
                'subject_id' => SubjectIdentity::id($subject),
            ],
            ['state_id' => $row->id],
        );
    }
}

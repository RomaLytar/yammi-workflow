<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Repository;

use Yammi\Workflow\Domain\Workflow\ArrayWorkflowDefinition;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\WorkflowDefinition;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

final class EloquentWorkflowDefinitionRepository implements WorkflowDefinitionRepository
{
    public function find(string $key): WorkflowDefinition
    {
        $workflow = WorkflowModel::query()->where('key', $key)->first();

        if ($workflow === null) {
            throw WorkflowNotFoundException::withKey($key);
        }

        $keyById = [];
        $states = [];
        $initial = null;

        foreach ($workflow->states()->orderBy('sort')->get() as $state) {
            $keyById[$state->id] = $state->key;
            $states[] = $state->key;

            if ($state->is_initial) {
                $initial = $state->key;
            }
        }

        $transitions = [];

        foreach ($workflow->transitionDefs()->get() as $definition) {
            $transitions[$keyById[$definition->from_state_id]][] = $keyById[$definition->to_state_id];
        }

        return ArrayWorkflowDefinition::fromArray($states, $transitions, $initial);
    }
}

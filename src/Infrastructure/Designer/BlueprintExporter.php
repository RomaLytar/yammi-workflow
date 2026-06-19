<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Designer;

use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

/**
 * Exports a workflow's current version as an importable blueprint spec (JSON-ready).
 */
final class BlueprintExporter
{
    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     initial: string,
     *     states: list<string>,
     *     transitions: array<string, list<string>>,
     *     conditions: array<string, list<array{field: string, operator: string, value: string}>>,
     *     actions: array<string, list<string>>,
     *     approval: array<string, list<string>>
     * }
     */
    public function export(string $key): array
    {
        $workflow = WorkflowModel::query()
            ->where('key', $key)
            ->where('is_current', true)
            ->first();

        if ($workflow === null) {
            throw WorkflowNotFoundException::withKey($key);
        }

        $states = [];
        $initial = '';
        $keyByStateId = [];

        foreach ($workflow->states()->orderBy('sort')->get() as $state) {
            $states[] = $state->key;
            $keyByStateId[$state->id] = $state->key;

            if ($state->is_initial) {
                $initial = $state->key;
            }
        }

        $transitions = [];
        $conditions = [];
        $actions = [];
        $approval = [];

        foreach ($workflow->transitionDefs()->get() as $def) {
            $from = $keyByStateId[$def->from_state_id] ?? '';
            $to = $keyByStateId[$def->to_state_id] ?? '';
            $edge = "{$from}>{$to}";

            $transitions[$from][] = $to;

            $edgeConditions = $def->conditions ?? [];
            $edgeActions = $def->actions ?? [];
            $edgeApproval = $def->approval ?? [];

            if ($edgeConditions !== []) {
                $conditions[$edge] = $edgeConditions;
            }

            if ($edgeActions !== []) {
                $actions[$edge] = $edgeActions;
            }

            if ($edgeApproval !== []) {
                $approval[$edge] = $edgeApproval;
            }
        }

        return [
            'key' => $workflow->key,
            'name' => $workflow->name,
            'initial' => $initial,
            'states' => $states,
            'transitions' => $transitions,
            'conditions' => $conditions,
            'actions' => $actions,
            'approval' => $approval,
        ];
    }
}

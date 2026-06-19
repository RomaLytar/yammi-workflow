<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Designer;

use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

/**
 * Loads a workflow's current version into the visual designer form shape.
 */
final class DesignerLoader
{
    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     states: list<array{key: string, initial: bool}>,
     *     transitions: list<array{from: string, to: string}>,
     *     conditions: list<array{from: string, to: string, field: string, operator: string, value: string}>,
     *     actions: list<array{from: string, to: string, action: string}>,
     *     approvals: list<array{from: string, to: string, steps: string}>
     * }
     */
    public function load(string $key): array
    {
        $workflow = WorkflowModel::query()
            ->where('key', $key)
            ->where('is_current', true)
            ->first();

        if ($workflow === null) {
            return [
                'key' => $key,
                'name' => $key,
                'states' => [],
                'transitions' => [],
                'conditions' => [],
                'actions' => [],
                'approvals' => [],
            ];
        }

        $keyByStateId = [];
        $states = [];

        foreach ($workflow->states()->orderBy('sort')->get() as $state) {
            $states[] = ['key' => $state->key, 'initial' => $state->is_initial];
            $keyByStateId[$state->id] = $state->key;
        }

        $transitions = [];
        $conditions = [];
        $actions = [];
        $approvals = [];

        foreach ($workflow->transitionDefs()->get() as $def) {
            $from = $keyByStateId[$def->from_state_id] ?? '';
            $to = $keyByStateId[$def->to_state_id] ?? '';

            $transitions[] = ['from' => $from, 'to' => $to];

            foreach ($def->conditions ?? [] as $rule) {
                $conditions[] = [
                    'from' => $from,
                    'to' => $to,
                    'field' => $rule['field'],
                    'operator' => $rule['operator'],
                    'value' => (string) $rule['value'],
                ];
            }

            foreach ($def->actions ?? [] as $action) {
                $actions[] = ['from' => $from, 'to' => $to, 'action' => (string) $action];
            }

            if (($def->approval ?? []) !== []) {
                $approvals[] = ['from' => $from, 'to' => $to, 'steps' => implode(', ', $def->approval)];
            }
        }

        return [
            'key' => $workflow->key,
            'name' => $workflow->name,
            'states' => $states,
            'transitions' => $transitions,
            'conditions' => $conditions,
            'actions' => $actions,
            'approvals' => $approvals,
        ];
    }
}

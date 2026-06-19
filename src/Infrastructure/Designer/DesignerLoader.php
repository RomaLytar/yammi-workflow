<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Designer;

use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

/**
 * Loads a workflow's current version back into the visual designer's shape.
 */
final class DesignerLoader
{
    /**
     * @return array{
     *     graph: array{key: string, name: string, nodes: list<array{id: string, key: string, initial: bool}>, edges: list<array{from: string, to: string}>},
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
                'graph' => ['key' => $key, 'name' => $key, 'nodes' => [], 'edges' => []],
                'conditions' => [],
                'actions' => [],
                'approvals' => [],
            ];
        }

        $keyByStateId = [];
        $nodes = [];

        foreach ($workflow->states()->orderBy('sort')->get() as $state) {
            $nodes[] = ['id' => 's'.$state->id, 'key' => $state->key, 'initial' => $state->is_initial];
            $keyByStateId[$state->id] = $state->key;
        }

        $edges = [];
        $conditions = [];
        $actions = [];
        $approvals = [];

        foreach ($workflow->transitionDefs()->get() as $def) {
            $from = $keyByStateId[$def->from_state_id] ?? '';
            $to = $keyByStateId[$def->to_state_id] ?? '';

            $edges[] = ['from' => 's'.$def->from_state_id, 'to' => 's'.$def->to_state_id];

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
            'graph' => ['key' => $workflow->key, 'name' => $workflow->name, 'nodes' => $nodes, 'edges' => $edges],
            'conditions' => $conditions,
            'actions' => $actions,
            'approvals' => $approvals,
        ];
    }
}

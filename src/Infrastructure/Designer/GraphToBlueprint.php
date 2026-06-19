<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Designer;

use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;

/**
 * Converts a visual editor graph (nodes + edges) into an importable blueprint.
 */
final class GraphToBlueprint
{
    /**
     * @param  array{key: string, name?: string, nodes: list<array{id: string, key: string, initial?: bool}>, edges: list<array{from: string, to: string}>}  $graph
     * @param  array<string, list<array{field: string, operator: string, value: string}>>  $conditions  keyed by "from>to"
     */
    public static function convert(array $graph, array $conditions = []): WorkflowBlueprintData
    {
        $idToKey = [];
        $states = [];
        $initial = null;

        foreach ($graph['nodes'] as $node) {
            $idToKey[$node['id']] = $node['key'];
            $states[] = $node['key'];

            if (($node['initial'] ?? false) && $initial === null) {
                $initial = $node['key'];
            }
        }

        $transitions = [];

        foreach ($graph['edges'] as $edge) {
            if (! isset($idToKey[$edge['from']], $idToKey[$edge['to']])) {
                continue;
            }

            $transitions[$idToKey[$edge['from']]][] = $idToKey[$edge['to']];
        }

        return new WorkflowBlueprintData(
            $graph['key'],
            $graph['name'] ?? $graph['key'],
            $states,
            $transitions,
            $initial ?? ($states[0] ?? ''),
            $conditions,
        );
    }
}

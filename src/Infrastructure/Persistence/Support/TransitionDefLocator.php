<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Support;

use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionDefModel;

/**
 * @internal
 */
final class TransitionDefLocator
{
    public static function find(int $workflowId, string $from, string $to): ?WorkflowTransitionDefModel
    {
        $fromId = self::stateId($workflowId, $from);
        $toId = self::stateId($workflowId, $to);

        if ($fromId === null || $toId === null) {
            return null;
        }

        return WorkflowTransitionDefModel::query()
            ->where('workflow_id', $workflowId)
            ->where('from_state_id', $fromId)
            ->where('to_state_id', $toId)
            ->first();
    }

    private static function stateId(int $workflowId, string $key): ?int
    {
        $id = WorkflowStateModel::query()
            ->where('workflow_id', $workflowId)
            ->where('key', $key)
            ->value('id');

        return $id === null ? null : (int) $id;
    }
}

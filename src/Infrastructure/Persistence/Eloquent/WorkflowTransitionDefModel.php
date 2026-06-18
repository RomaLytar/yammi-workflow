<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $workflow_id
 * @property int $from_state_id
 * @property int $to_state_id
 *
 * @internal
 */
final class WorkflowTransitionDefModel extends Model
{
    public $timestamps = false;

    protected $fillable = ['workflow_id', 'from_state_id', 'to_state_id'];

    protected $casts = [
        'workflow_id' => 'integer',
        'from_state_id' => 'integer',
        'to_state_id' => 'integer',
    ];

    public function getTable(): string
    {
        return (string) config('workflow.tables.transition_defs', 'workflow_transition_defs');
    }
}

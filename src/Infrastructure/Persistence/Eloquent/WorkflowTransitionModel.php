<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $subject_type
 * @property string $subject_id
 * @property int $workflow_id
 * @property int $from_state_id
 * @property int $to_state_id
 * @property string|null $actor_type
 * @property string|null $actor_id
 *
 * @internal
 */
final class WorkflowTransitionModel extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'workflow_id',
        'from_state_id',
        'to_state_id',
        'actor_type',
        'actor_id',
    ];

    protected $casts = [
        'workflow_id' => 'integer',
        'from_state_id' => 'integer',
        'to_state_id' => 'integer',
    ];

    public function getTable(): string
    {
        return (string) config('workflow.tables.transitions', 'workflow_transitions');
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $subject_type
 * @property string $subject_id
 * @property int $state_id
 *
 * @internal
 */
final class WorkflowInstanceModel extends Model
{
    protected $fillable = ['subject_type', 'subject_id', 'state_id'];

    protected $casts = [
        'state_id' => 'integer',
    ];

    public function getTable(): string
    {
        return (string) config('workflow.tables.instances', 'workflow_instances');
    }
}

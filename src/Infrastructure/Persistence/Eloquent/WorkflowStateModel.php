<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $workflow_id
 * @property string $key
 * @property string $label
 * @property int $sort
 * @property bool $is_initial
 *
 * @internal
 */
final class WorkflowStateModel extends Model
{
    public $timestamps = false;

    protected $fillable = ['workflow_id', 'key', 'label', 'sort', 'is_initial'];

    protected $casts = [
        'workflow_id' => 'integer',
        'sort' => 'integer',
        'is_initial' => 'boolean',
    ];

    public function getTable(): string
    {
        return (string) config('workflow.tables.states', 'workflow_states');
    }
}

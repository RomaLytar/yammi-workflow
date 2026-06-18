<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property int $version
 * @property bool $is_current
 *
 * @internal
 */
final class WorkflowModel extends Model
{
    protected $fillable = ['key', 'name', 'version', 'is_current', 'tenant_id'];

    protected $casts = [
        'version' => 'integer',
        'is_current' => 'boolean',
    ];

    public function getTable(): string
    {
        return (string) config('workflow.tables.workflows', 'workflows');
    }

    /**
     * @return HasMany<WorkflowStateModel>
     */
    public function states(): HasMany
    {
        return $this->hasMany(WorkflowStateModel::class, 'workflow_id');
    }

    /**
     * @return HasMany<WorkflowTransitionDefModel>
     */
    public function transitionDefs(): HasMany
    {
        return $this->hasMany(WorkflowTransitionDefModel::class, 'workflow_id');
    }
}

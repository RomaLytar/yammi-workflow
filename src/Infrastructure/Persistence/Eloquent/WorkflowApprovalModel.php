<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;

/**
 * @property int $id
 * @property string $subject_type
 * @property string $subject_id
 * @property int $step
 * @property string $label
 * @property ApprovalStatus $status
 * @property string|null $decided_by_type
 * @property string|null $decided_by_id
 * @property string|null $comment
 * @property Carbon|null $decided_at
 *
 * @internal
 */
final class WorkflowApprovalModel extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'step',
        'label',
        'status',
        'decided_by_type',
        'decided_by_id',
        'comment',
        'decided_at',
        'tenant_id',
    ];

    protected $casts = [
        'step' => 'integer',
        'status' => ApprovalStatus::class,
        'decided_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('workflow.tables.approvals', 'workflow_approvals');
    }
}

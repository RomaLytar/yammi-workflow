<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Concerns\HasWorkflow;
use Yammi\Workflow\Contracts\WorkflowSubject;

/**
 * @property int $id
 * @property string|null $workflow_state
 */
final class Invoice extends Model implements WorkflowSubject
{
    use HasWorkflow;

    public $timestamps = false;

    protected $table = 'invoices';

    protected $guarded = [];

    public function workflowTitle(): string
    {
        return sprintf('INV-%03d', $this->id);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Concerns\HasWorkflow;

/**
 * @property int $id
 * @property string|null $workflow_state
 */
final class Invoice extends Model
{
    use HasWorkflow;

    public $timestamps = false;

    protected $table = 'invoices';

    protected $guarded = [];
}

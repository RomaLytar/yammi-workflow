<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\ApprovalStepData;

interface ApprovalNotifier
{
    /**
     * Tell the step's assignee that it is now their turn to decide.
     */
    public function assigned(object $subject, ApprovalStepData $step): void;
}

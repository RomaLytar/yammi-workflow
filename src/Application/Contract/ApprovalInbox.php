<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\ApprovalStepData;

interface ApprovalInbox
{
    /**
     * The current pending steps assigned to the given actor.
     *
     * @return list<ApprovalStepData>
     */
    public function assignedTo(string $assigneeType, string $assigneeId): array;
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Domain\Approval\Enum;

enum ApprovalStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }

    public function isRejected(): bool
    {
        return $this === self::Rejected;
    }
}

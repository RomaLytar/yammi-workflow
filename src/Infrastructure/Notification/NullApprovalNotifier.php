<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Notification;

use Yammi\Workflow\Application\Contract\ApprovalNotifier;
use Yammi\Workflow\Application\DTO\ApprovalStepData;

/**
 * @internal
 */
final class NullApprovalNotifier implements ApprovalNotifier
{
    public function assigned(object $subject, ApprovalStepData $step): void {}
}

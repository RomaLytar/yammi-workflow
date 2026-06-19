<?php

declare(strict_types=1);

namespace Yammi\Workflow\Facade;

use Illuminate\Support\Facades\Facade;
use Yammi\Workflow\Infrastructure\Api\ApprovalManager;

/**
 * @method static void request(object $subject, array $steps)
 * @method static \Yammi\Workflow\Application\DTO\ApprovalSummaryData approve(object $subject, ?string $comment = null)
 * @method static \Yammi\Workflow\Application\DTO\ApprovalSummaryData reject(object $subject, ?string $comment = null)
 * @method static \Yammi\Workflow\Application\DTO\ApprovalSummaryData for(object $subject)
 * @method static list<\Yammi\Workflow\Application\DTO\ApprovalStepData> pendingFor(\Illuminate\Contracts\Auth\Authenticatable $user)
 *
 * @see ApprovalManager
 */
final class Approval extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApprovalManager::class;
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Facade;

use Illuminate\Support\Facades\Facade;
use Yammi\Workflow\Infrastructure\Api\WorkflowManager;

/**
 * @method static \Yammi\Workflow\Application\DTO\WorkflowSnapshotData for(object $subject)
 * @method static string currentState(object $subject)
 * @method static list<string> allowedTransitions(object $subject)
 * @method static list<\Yammi\Workflow\Application\DTO\TransitionRecordData> history(object $subject)
 * @method static \Yammi\Workflow\Application\DTO\TransitionResultData transition(object $subject, string $to, ?string $reason = null, array $meta = [])
 * @method static array<string, int> metrics(string $workflow)
 *
 * @see WorkflowManager
 */
final class Workflow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WorkflowManager::class;
    }
}

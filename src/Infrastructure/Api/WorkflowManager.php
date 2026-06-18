<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Api;

use Yammi\Workflow\Application\Action\AllowedTransitionsAction;
use Yammi\Workflow\Application\Action\CurrentStateAction;
use Yammi\Workflow\Application\Action\SnapshotWorkflowAction;
use Yammi\Workflow\Application\Action\TransitionStateAction;
use Yammi\Workflow\Application\Contract\TransitionHistory;
use Yammi\Workflow\Application\DTO\TransitionRecordData;
use Yammi\Workflow\Application\DTO\TransitionResultData;
use Yammi\Workflow\Application\DTO\WorkflowSnapshotData;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class WorkflowManager
{
    public function __construct(
        private readonly SnapshotWorkflowAction $snapshot,
        private readonly TransitionStateAction $transition,
        private readonly CurrentStateAction $current,
        private readonly AllowedTransitionsAction $allowed,
        private readonly TransitionHistory $history,
    ) {}

    public function for(object $subject): WorkflowSnapshotData
    {
        return ($this->snapshot)($subject);
    }

    public function currentState(object $subject): string
    {
        return ($this->current)($subject)->name;
    }

    /**
     * @return list<string>
     */
    public function allowedTransitions(object $subject): array
    {
        return array_map(
            static fn (State $state): string => $state->name,
            ($this->allowed)($subject),
        );
    }

    /**
     * @return list<TransitionRecordData>
     */
    public function history(object $subject): array
    {
        return $this->history->forSubject($subject);
    }

    public function transition(object $subject, string $to): TransitionResultData
    {
        return ($this->transition)($subject, $to);
    }
}

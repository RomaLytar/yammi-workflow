<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class WorkflowSnapshotData
{
    /**
     * @param  list<string>  $allowed
     * @param  list<TransitionRecordData>  $history
     */
    public function __construct(
        public readonly string $workflow,
        public readonly string $current,
        public readonly array $allowed,
        public readonly array $history,
    ) {}
}

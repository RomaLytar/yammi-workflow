<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\TransitionRecordData;

interface TransitionHistory
{
    /**
     * @return list<TransitionRecordData>
     */
    public function forSubject(object $subject): array;
}

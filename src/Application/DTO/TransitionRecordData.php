<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class TransitionRecordData
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly ?string $actorType,
        public readonly ?string $actorId,
        public readonly string $at,
    ) {}
}

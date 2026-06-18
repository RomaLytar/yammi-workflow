<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class TransitionRecordData
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly ?string $actorType,
        public readonly ?string $actorId,
        public readonly string $at,
        public readonly ?string $reason = null,
        public readonly array $meta = [],
    ) {}
}

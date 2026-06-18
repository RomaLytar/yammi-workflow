<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class ActorData
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
    ) {}
}

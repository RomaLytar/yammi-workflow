<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\DTO;

final class TransitionResultData
{
    public function __construct(
        public readonly string $workflow,
        public readonly string $from,
        public readonly string $to,
    ) {}
}

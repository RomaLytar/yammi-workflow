<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

use Yammi\Workflow\Application\DTO\ActorData;

interface ActorResolver
{
    public function resolve(): ?ActorData;
}

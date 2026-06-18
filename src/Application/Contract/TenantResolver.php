<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface TenantResolver
{
    public function current(): ?string;
}

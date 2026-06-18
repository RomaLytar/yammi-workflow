<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Support;

use Yammi\Workflow\Application\Contract\TenantResolver;

final class FixedTenantResolver implements TenantResolver
{
    public function __construct(
        private ?string $tenant = null,
    ) {}

    public function set(?string $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function current(): ?string
    {
        return $this->tenant;
    }
}

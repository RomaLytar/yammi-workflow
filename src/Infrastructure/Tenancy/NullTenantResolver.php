<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Tenancy;

use Yammi\Workflow\Application\Contract\TenantResolver;

/**
 * @internal
 */
final class NullTenantResolver implements TenantResolver
{
    public function current(): ?string
    {
        return null;
    }
}

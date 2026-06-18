<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Infrastructure\Guard\InMemoryGuardRegistry;

/**
 * @internal
 */
final class GuardBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->singleton(InMemoryGuardRegistry::class);
        $this->app->alias(InMemoryGuardRegistry::class, GuardRegistry::class);
    }
}

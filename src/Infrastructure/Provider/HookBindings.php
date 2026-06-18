<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\HookRegistry;
use Yammi\Workflow\Infrastructure\Hook\InMemoryHookRegistry;

/**
 * @internal
 */
final class HookBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->singleton(InMemoryHookRegistry::class);
        $this->app->alias(InMemoryHookRegistry::class, HookRegistry::class);
    }
}

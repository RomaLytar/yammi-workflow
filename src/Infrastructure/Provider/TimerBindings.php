<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Infrastructure\Timer\TimerRegistry;

/**
 * @internal
 */
final class TimerBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->singleton(TimerRegistry::class);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\TransitionAuthorizer;
use Yammi\Workflow\Infrastructure\Authorization\GateTransitionAuthorizer;

/**
 * @internal
 */
final class AuthorizationBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(TransitionAuthorizer::class, function (Application $app): GateTransitionAuthorizer {
            return new GateTransitionAuthorizer(
                $app->make(AuthFactory::class),
                $app->make(Gate::class),
                (string) config('workflow.authorization.ability', 'workflow.transition'),
            );
        });
    }
}

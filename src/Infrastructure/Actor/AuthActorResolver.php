<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Actor;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Yammi\Workflow\Application\Contract\ActorResolver;
use Yammi\Workflow\Application\DTO\ActorData;

/**
 * @internal
 */
final class AuthActorResolver implements ActorResolver
{
    public function __construct(
        private readonly AuthFactory $auth,
    ) {}

    public function resolve(): ?ActorData
    {
        $user = $this->auth->guard()->user();

        if ($user === null) {
            return null;
        }

        return new ActorData($user::class, (string) $user->getAuthIdentifier());
    }
}

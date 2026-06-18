<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Authorization;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Yammi\Workflow\Application\Contract\TransitionAuthorizer;

/**
 * @internal
 */
final class GateTransitionAuthorizer implements TransitionAuthorizer
{
    public function __construct(
        private readonly AuthFactory $auth,
        private readonly Gate $gate,
        private readonly string $ability,
    ) {}

    public function allows(object $subject, string $from, string $to): bool
    {
        $user = $this->auth->guard()->user();

        if ($user === null) {
            return true;
        }

        if (! $this->gate->has($this->ability)) {
            return true;
        }

        return $this->gate->forUser($user)->allows($this->ability, [$subject, $from, $to]);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Guard;

use Yammi\Workflow\Application\Contract\TransitionGuard;

/**
 * @internal
 */
final class CallableGuard implements TransitionGuard
{
    /** @var callable(object, string, string): bool */
    private $callback;

    /**
     * @param  callable(object, string, string): bool  $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function passes(object $subject, string $from, string $to): bool
    {
        return (bool) ($this->callback)($subject, $from, $to);
    }
}

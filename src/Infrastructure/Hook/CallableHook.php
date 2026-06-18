<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Hook;

use Yammi\Workflow\Application\Contract\TransitionHook;

/**
 * @internal
 */
final class CallableHook implements TransitionHook
{
    /** @var callable(object, string, string): void */
    private $callback;

    /**
     * @param  callable(object, string, string): void  $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function run(object $subject, string $from, string $to): void
    {
        ($this->callback)($subject, $from, $to);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Concerns;

use Yammi\Workflow\Application\Action\AllowedTransitionsAction;
use Yammi\Workflow\Application\Action\CurrentStateAction;
use Yammi\Workflow\Application\Action\TransitionStateAction;
use Yammi\Workflow\Application\DTO\TransitionResultData;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

trait HasWorkflow
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function transitionTo(string $state, ?string $reason = null, array $meta = []): TransitionResultData
    {
        return app(TransitionStateAction::class)($this, $state, $reason, $meta);
    }

    public function currentState(): string
    {
        return app(CurrentStateAction::class)($this)->name;
    }

    /**
     * @return list<string>
     */
    public function allowedTransitions(): array
    {
        return array_map(
            static fn (State $state): string => $state->name,
            app(AllowedTransitionsAction::class)($this),
        );
    }
}

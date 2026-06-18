<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ActorResolver;
use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Application\Contract\HookRegistry;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\TransitionRecorder;
use Yammi\Workflow\Application\Contract\WorkflowEventDispatcher;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Application\DTO\TransitionResultData;
use Yammi\Workflow\Domain\Workflow\Exception\TransitionBlockedException;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Events\WorkflowTransitioned;

final class TransitionStateAction
{
    public function __construct(
        private readonly WorkflowDefinitionRepository $definitions,
        private readonly StateStore $states,
        private readonly WorkflowKeyResolver $keys,
        private readonly TransitionRecorder $recorder,
        private readonly WorkflowEventDispatcher $events,
        private readonly ActorResolver $actors,
        private readonly GuardRegistry $guards,
        private readonly HookRegistry $hooks,
    ) {}

    public function __invoke(object $subject, string $to): TransitionResultData
    {
        $key = $this->keys->keyFor($subject);
        $definition = $this->definitions->find($key);

        $current = $this->states->current($subject) ?? $definition->initialState();
        $next = (new StateMachine($definition))->transition($current, new State($to));

        if (! $this->guards->allows($key, $subject, $current->name, $next->name)) {
            throw TransitionBlockedException::forTransition($key, $current, $next);
        }

        $this->states->put($subject, $next);

        $actor = $this->actors->resolve();
        $this->recorder->record($subject, $key, $current, $next, $actor);
        $this->events->dispatch(new WorkflowTransitioned($subject, $key, $current->name, $next->name, $actor));
        $this->hooks->run($key, $subject, $current->name, $next->name);

        return new TransitionResultData($key, $current->name, $next->name);
    }
}

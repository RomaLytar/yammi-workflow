<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Action;

use Yammi\Workflow\Application\Contract\ActorResolver;
use Yammi\Workflow\Application\Contract\GuardRegistry;
use Yammi\Workflow\Application\Contract\HookRegistry;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\TransitionActionRunner;
use Yammi\Workflow\Application\Contract\TransitionAuthorizer;
use Yammi\Workflow\Application\Contract\TransitionConditionChecker;
use Yammi\Workflow\Application\Contract\TransitionRecorder;
use Yammi\Workflow\Application\Contract\WorkflowEventDispatcher;
use Yammi\Workflow\Application\DTO\TransitionResultData;
use Yammi\Workflow\Application\Service\SubjectDefinitionResolver;
use Yammi\Workflow\Domain\Workflow\Exception\TransitionBlockedException;
use Yammi\Workflow\Domain\Workflow\Exception\UnauthorizedTransitionException;
use Yammi\Workflow\Domain\Workflow\StateMachine;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Events\WorkflowTransitioned;

final class TransitionStateAction
{
    public function __construct(
        private readonly SubjectDefinitionResolver $resolver,
        private readonly StateStore $states,
        private readonly TransitionRecorder $recorder,
        private readonly WorkflowEventDispatcher $events,
        private readonly ActorResolver $actors,
        private readonly GuardRegistry $guards,
        private readonly HookRegistry $hooks,
        private readonly TransitionAuthorizer $authorizer,
        private readonly TransitionConditionChecker $conditions,
        private readonly TransitionActionRunner $actions,
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public function __invoke(object $subject, string $to, ?string $reason = null, array $meta = []): TransitionResultData
    {
        $resolved = $this->resolver->resolve($subject);
        $key = $resolved->key;

        $current = $this->states->current($subject) ?? $resolved->definition->initialState();
        $next = (new StateMachine($resolved->definition))->transition($current, new State($to));

        if (! $this->guards->allows($key, $subject, $current->name, $next->name)) {
            throw TransitionBlockedException::forTransition($key, $current, $next);
        }

        if (! $this->conditions->satisfied($resolved->workflowId, $current->name, $next->name, $subject)) {
            throw TransitionBlockedException::forTransition($key, $current, $next);
        }

        if (! $this->authorizer->allows($subject, $current->name, $next->name)) {
            throw UnauthorizedTransitionException::forTransition($key, $current, $next);
        }

        $this->states->put($subject, $next, $resolved->workflowId);

        $actor = $this->actors->resolve();
        $this->recorder->record($subject, $resolved->workflowId, $current, $next, $actor, $reason, $meta);
        $this->events->dispatch(new WorkflowTransitioned($subject, $key, $current->name, $next->name, $actor, $reason, $meta));
        $this->hooks->run($key, $subject, $current->name, $next->name);
        $this->actions->run($resolved->workflowId, $current->name, $next->name, $subject);

        return new TransitionResultData($key, $current->name, $next->name);
    }
}

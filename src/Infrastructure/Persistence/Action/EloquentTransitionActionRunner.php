<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Persistence\Action;

use Yammi\Workflow\Application\Contract\ActionCatalog;
use Yammi\Workflow\Application\Contract\TransitionActionRunner;
use Yammi\Workflow\Infrastructure\Persistence\Support\TransitionDefLocator;

/**
 * @internal
 */
final class EloquentTransitionActionRunner implements TransitionActionRunner
{
    public function __construct(
        private readonly ActionCatalog $catalog,
    ) {}

    public function run(int $workflowId, string $from, string $to, object $subject): void
    {
        $names = TransitionDefLocator::find($workflowId, $from, $to)?->actions ?? [];

        foreach ($names as $name) {
            $this->catalog->run((string) $name, $subject);
        }
    }
}

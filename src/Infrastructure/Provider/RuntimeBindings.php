<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Infrastructure\Resolver\ConfigWorkflowKeyResolver;
use Yammi\Workflow\Infrastructure\StateStore\ColumnStateStore;
use Yammi\Workflow\Infrastructure\StateStore\InstanceStateStore;

/**
 * @internal
 */
final class RuntimeBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(WorkflowKeyResolver::class, function (): ConfigWorkflowKeyResolver {
            /** @var array<string, string> $map */
            $map = (array) config('workflow.subjects', []);

            return new ConfigWorkflowKeyResolver($map);
        });

        $this->app->bind(StateStore::class, function (Application $app): StateStore {
            $driver = (string) config('workflow.state_store.driver', 'instance');

            if ($driver === 'column') {
                return new ColumnStateStore((string) config('workflow.state_store.column', 'workflow_state'));
            }

            return $app->make(InstanceStateStore::class);
        });
    }
}

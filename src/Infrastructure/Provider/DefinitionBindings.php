<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Persistence\Repository\EloquentWorkflowDefinitionRepository;

/**
 * @internal
 */
final class DefinitionBindings
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(WorkflowDefinitionRepository::class, EloquentWorkflowDefinitionRepository::class);
        $this->app->singleton(WorkflowImporter::class);
    }
}

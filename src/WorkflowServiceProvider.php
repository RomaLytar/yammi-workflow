<?php

declare(strict_types=1);

namespace Yammi\Workflow;

use Illuminate\Support\ServiceProvider;
use Yammi\Workflow\Infrastructure\Console\TickWorkflowsCommand;
use Yammi\Workflow\Infrastructure\Provider\ApprovalBindings;
use Yammi\Workflow\Infrastructure\Provider\AuthorizationBindings;
use Yammi\Workflow\Infrastructure\Provider\DefinitionBindings;
use Yammi\Workflow\Infrastructure\Provider\GuardBindings;
use Yammi\Workflow\Infrastructure\Provider\HistoryBindings;
use Yammi\Workflow\Infrastructure\Provider\HookBindings;
use Yammi\Workflow\Infrastructure\Provider\ReadBindings;
use Yammi\Workflow\Infrastructure\Provider\RuntimeBindings;
use Yammi\Workflow\Infrastructure\Provider\TimerBindings;

final class WorkflowServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__.'/../config/workflow.php';

    private const MIGRATIONS_PATH = __DIR__.'/../database/migrations';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'workflow');

        (new DefinitionBindings($this->app))->register();
        (new RuntimeBindings($this->app))->register();
        (new HistoryBindings($this->app))->register();
        (new ReadBindings($this->app))->register();
        (new GuardBindings($this->app))->register();
        (new HookBindings($this->app))->register();
        (new AuthorizationBindings($this->app))->register();
        (new TimerBindings($this->app))->register();
        (new ApprovalBindings($this->app))->register();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(self::MIGRATIONS_PATH);

        if ($this->app->runningInConsole()) {
            $this->commands([TickWorkflowsCommand::class]);

            $this->publishes(
                [self::CONFIG_PATH => config_path('workflow.php')],
                'workflow-config',
            );

            $this->publishes(
                [self::MIGRATIONS_PATH => database_path('migrations')],
                'workflow-migrations',
            );
        }
    }
}

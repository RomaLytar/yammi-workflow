<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Yammi\Workflow\Filament\Pages\WorkflowDesigner;
use Yammi\Workflow\Filament\Resources\WorkflowApprovalResource;
use Yammi\Workflow\Filament\Resources\WorkflowInstanceResource;
use Yammi\Workflow\Filament\Widgets\StateDistributionWidget;

final class WorkflowPlugin implements Plugin
{
    public function getId(): string
    {
        return 'yammi-workflow';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            WorkflowInstanceResource::class,
            WorkflowApprovalResource::class,
        ]);

        $panel->pages([
            WorkflowDesigner::class,
        ]);

        $panel->widgets([
            StateDistributionWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): self
    {
        return new self;
    }
}

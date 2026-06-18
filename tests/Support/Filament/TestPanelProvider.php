<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Support\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Yammi\Workflow\Filament\WorkflowPlugin;

final class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('testing')
            ->path('testing')
            ->plugin(WorkflowPlugin::make());
    }
}

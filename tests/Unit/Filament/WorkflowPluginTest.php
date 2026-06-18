<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Filament;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Filament\WorkflowPlugin;

final class WorkflowPluginTest extends TestCase
{
    public function test_it_exposes_a_stable_plugin_id(): void
    {
        $this->assertSame('yammi-workflow', WorkflowPlugin::make()->getId());
    }
}

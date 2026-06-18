<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration;

use Illuminate\Support\ServiceProvider;
use Yammi\Workflow\Tests\TestCase;

final class PackageBootTest extends TestCase
{
    public function test_it_merges_the_package_config(): void
    {
        $this->assertTrue(config('workflow.enabled'));
        $this->assertSame('workflow_instances', config('workflow.tables.instances'));
        $this->assertSame('workflow_transitions', config('workflow.tables.transitions'));
        $this->assertSame('workflow_approvals', config('workflow.tables.approvals'));
    }

    public function test_it_publishes_the_config_under_a_tag(): void
    {
        $this->assertArrayHasKey(
            'workflow-config',
            ServiceProvider::$publishGroups,
        );
    }
}

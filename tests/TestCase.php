<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Yammi\Workflow\WorkflowServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [WorkflowServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

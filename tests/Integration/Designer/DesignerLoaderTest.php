<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Designer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Designer\DesignerLoader;
use Yammi\Workflow\Tests\TestCase;

final class DesignerLoaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_a_workflow_into_the_designer_shape(): void
    {
        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'name' => 'Invoice approval',
            'states' => ['draft', 'pending', 'approved'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending'], 'pending' => ['approved']],
            'conditions' => ['draft>pending' => [['field' => 'amount', 'operator' => 'gte', 'value' => '1000']]],
            'actions' => ['pending>approved' => ['notify']],
            'approval' => ['pending>approved' => ['manager', 'finance']],
        ]));

        $data = $this->app->make(DesignerLoader::class)->load('invoice');

        $this->assertSame(['draft', 'pending', 'approved'], array_column($data['graph']['nodes'], 'key'));
        $this->assertCount(2, $data['graph']['edges']);
        $this->assertTrue($data['graph']['nodes'][0]['initial']);

        $this->assertSame(
            [['from' => 'draft', 'to' => 'pending', 'field' => 'amount', 'operator' => 'gte', 'value' => '1000']],
            $data['conditions'],
        );
        $this->assertSame([['from' => 'pending', 'to' => 'approved', 'action' => 'notify']], $data['actions']);
        $this->assertSame([['from' => 'pending', 'to' => 'approved', 'steps' => 'manager, finance']], $data['approvals']);
    }

    public function test_an_unknown_workflow_loads_blank(): void
    {
        $data = $this->app->make(DesignerLoader::class)->load('ghost');

        $this->assertSame([], $data['graph']['nodes']);
        $this->assertSame([], $data['conditions']);
    }
}

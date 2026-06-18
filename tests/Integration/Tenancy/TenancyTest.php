<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Integration\Tenancy;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Yammi\Workflow\Application\Contract\TenantResolver;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Exception\WorkflowNotFoundException;
use Yammi\Workflow\Domain\Workflow\Repository\WorkflowDefinitionRepository;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Tests\Support\FixedTenantResolver;
use Yammi\Workflow\Tests\TestCase;

final class TenancyTest extends TestCase
{
    use RefreshDatabase;

    private FixedTenantResolver $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = new FixedTenantResolver;
        $this->app->instance(TenantResolver::class, $this->tenant);
    }

    private function importInvoice(): void
    {
        $this->app->make(WorkflowImporter::class)->import(WorkflowBlueprintData::fromArray([
            'key' => 'invoice',
            'states' => ['draft', 'pending'],
            'initial' => 'draft',
            'transitions' => ['draft' => ['pending']],
        ]));
    }

    private function repository(): WorkflowDefinitionRepository
    {
        return $this->app->make(WorkflowDefinitionRepository::class);
    }

    public function test_it_stamps_the_tenant_on_create(): void
    {
        $this->tenant->set('tenant-a');
        $this->importInvoice();

        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'tenant_id' => 'tenant-a']);
    }

    public function test_definitions_are_isolated_between_tenants(): void
    {
        $this->tenant->set('tenant-a');
        $this->importInvoice();

        $this->assertNotEmpty($this->repository()->find('invoice')->states());

        $this->tenant->set('tenant-b');

        $this->expectException(WorkflowNotFoundException::class);
        $this->repository()->find('invoice');
    }

    public function test_a_null_tenant_sees_everything(): void
    {
        $this->tenant->set(null);
        $this->importInvoice();

        $this->assertNotEmpty($this->repository()->find('invoice')->states());
        $this->assertDatabaseHas('workflows', ['key' => 'invoice', 'tenant_id' => null]);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Provider;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Application\Contract\TenantResolver;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowApprovalModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowInstanceModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionModel;
use Yammi\Workflow\Infrastructure\Tenancy\NullTenantResolver;

/**
 * @internal
 */
final class TenancyBindings
{
    /** @var list<class-string<Model>> */
    private const MODELS = [
        WorkflowModel::class,
        WorkflowInstanceModel::class,
        WorkflowTransitionModel::class,
        WorkflowApprovalModel::class,
    ];

    public function __construct(
        private readonly Application $app,
    ) {}

    public function register(): void
    {
        $this->app->bind(TenantResolver::class, NullTenantResolver::class);
    }

    public function boot(): void
    {
        $app = $this->app;
        $column = (string) config('workflow.tenancy.column', 'tenant_id');

        foreach (self::MODELS as $model) {
            $model::addGlobalScope('workflow-tenant', static function (Builder $query) use ($app, $column): void {
                $tenant = $app->make(TenantResolver::class)->current();

                if ($tenant !== null) {
                    $query->where($query->qualifyColumn($column), $tenant);
                }
            });

            $model::creating(static function (Model $record) use ($app, $column): void {
                $tenant = $app->make(TenantResolver::class)->current();

                if ($tenant !== null && $record->getAttribute($column) === null) {
                    $record->setAttribute($column, $tenant);
                }
            });
        }
    }
}

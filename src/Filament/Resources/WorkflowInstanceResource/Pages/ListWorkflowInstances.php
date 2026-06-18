<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Resources\WorkflowInstanceResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Yammi\Workflow\Filament\Resources\WorkflowInstanceResource;

final class ListWorkflowInstances extends ListRecords
{
    protected static string $resource = WorkflowInstanceResource::class;
}

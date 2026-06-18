<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Resources\WorkflowApprovalResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Yammi\Workflow\Filament\Resources\WorkflowApprovalResource;

final class ListWorkflowApprovals extends ListRecords
{
    protected static string $resource = WorkflowApprovalResource::class;
}

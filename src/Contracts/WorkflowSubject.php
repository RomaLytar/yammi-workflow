<?php

declare(strict_types=1);

namespace Yammi\Workflow\Contracts;

interface WorkflowSubject
{
    public function workflowTitle(): string;
}

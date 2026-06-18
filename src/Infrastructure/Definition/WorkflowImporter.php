<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Definition;

use Illuminate\Database\ConnectionResolverInterface;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\ArrayWorkflowDefinition;
use Yammi\Workflow\Domain\Workflow\Exception\InvalidWorkflowDefinitionException;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTransitionDefModel;

final class WorkflowImporter
{
    public function __construct(
        private readonly ConnectionResolverInterface $connections,
    ) {}

    public function import(WorkflowBlueprintData $blueprint): void
    {
        ArrayWorkflowDefinition::fromArray($blueprint->states, $blueprint->transitions);

        if (! in_array($blueprint->initial, $blueprint->states, true)) {
            throw InvalidWorkflowDefinitionException::unknownInitialState($blueprint->initial);
        }

        $this->connections->connection()->transaction(function () use ($blueprint): void {
            $workflow = WorkflowModel::create([
                'key' => $blueprint->key,
                'name' => $blueprint->name,
            ]);

            $stateIds = [];
            $sort = 0;

            foreach ($blueprint->states as $stateKey) {
                $state = WorkflowStateModel::create([
                    'workflow_id' => $workflow->id,
                    'key' => $stateKey,
                    'label' => $stateKey,
                    'sort' => $sort,
                    'is_initial' => $stateKey === $blueprint->initial,
                ]);

                $stateIds[$stateKey] = $state->id;
                $sort++;
            }

            foreach ($blueprint->transitions as $from => $targets) {
                foreach ($targets as $to) {
                    WorkflowTransitionDefModel::create([
                        'workflow_id' => $workflow->id,
                        'from_state_id' => $stateIds[$from],
                        'to_state_id' => $stateIds[$to],
                    ]);
                }
            }
        });
    }
}

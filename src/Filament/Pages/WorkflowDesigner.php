<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionOperator;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Designer\GraphToBlueprint;

class WorkflowDesigner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Designer';

    protected static ?string $title = 'Workflow designer';

    protected static string $view = 'workflow::filament.pages.workflow-designer';

    /**
     * @var array<string, mixed>
     */
    public array $graph = [
        'key' => 'invoice',
        'name' => 'Invoice approval',
        'nodes' => [
            ['id' => 'n1', 'key' => 'draft', 'initial' => true],
            ['id' => 'n2', 'key' => 'pending'],
            ['id' => 'n3', 'key' => 'approved'],
        ],
        'edges' => [
            ['from' => 'n1', 'to' => 'n2'],
            ['from' => 'n2', 'to' => 'n3'],
        ],
    ];

    /**
     * @var list<array{from: string, to: string, field: string, operator: string, value: string}>
     */
    public array $conditions = [];

    public function addRule(): void
    {
        $this->conditions[] = ['from' => '', 'to' => '', 'field' => '', 'operator' => 'eq', 'value' => ''];
    }

    public function removeRule(int $index): void
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    public function save(): void
    {
        /** @var array{key: string, name?: string, nodes: list<array{id: string, key: string, initial?: bool}>, edges: list<array{from: string, to: string}>} $graph */
        $graph = $this->graph;

        app(WorkflowImporter::class)->import(GraphToBlueprint::convert($graph, $this->conditionMap()));

        Notification::make()
            ->title('Workflow saved as a new version')
            ->success()
            ->send();
    }

    /**
     * @return array<string, string>
     */
    public function stateOptions(): array
    {
        $options = [];

        /** @var list<array{id: string, key: string, initial?: bool}> $nodes */
        $nodes = $this->graph['nodes'] ?? [];

        foreach ($nodes as $node) {
            $options[$node['key']] = $node['key'];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function operatorOptions(): array
    {
        $options = [];

        foreach (ConditionOperator::cases() as $operator) {
            $options[$operator->value] = $operator->label();
        }

        return $options;
    }

    /**
     * @return array<string, list<array{field: string, operator: string, value: string}>>
     */
    private function conditionMap(): array
    {
        $map = [];

        foreach ($this->conditions as $rule) {
            if ($rule['from'] === '' || $rule['to'] === '' || $rule['field'] === '') {
                continue;
            }

            $map["{$rule['from']}>{$rule['to']}"][] = [
                'field' => $rule['field'],
                'operator' => $rule['operator'],
                'value' => (string) $rule['value'],
            ];
        }

        return $map;
    }
}

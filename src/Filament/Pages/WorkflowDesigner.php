<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Yammi\Workflow\Application\Contract\ActionCatalog;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionOperator;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Designer\DesignerLoader;
use Yammi\Workflow\Infrastructure\Designer\GraphToBlueprint;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

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

    /**
     * @var list<array{from: string, to: string, action: string}>
     */
    public array $actions = [];

    /**
     * @var list<array{from: string, to: string, steps: string}>
     */
    public array $approvals = [];

    public string $workflowKey = '';

    public function load(): void
    {
        if ($this->workflowKey === '') {
            return;
        }

        $data = app(DesignerLoader::class)->load($this->workflowKey);

        $this->graph = $data['graph'];
        $this->conditions = $data['conditions'];
        $this->actions = $data['actions'];
        $this->approvals = $data['approvals'];

        $this->dispatch('workflow-loaded');
    }

    /**
     * @return array<string, string>
     */
    public function workflowOptions(): array
    {
        /** @var array<string, string> $options */
        $options = WorkflowModel::query()
            ->where('is_current', true)
            ->orderBy('key')
            ->pluck('key', 'key')
            ->all();

        return $options;
    }

    public function addRule(): void
    {
        $this->conditions[] = ['from' => '', 'to' => '', 'field' => '', 'operator' => 'eq', 'value' => ''];
    }

    public function removeRule(int $index): void
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    public function addAction(): void
    {
        $this->actions[] = ['from' => '', 'to' => '', 'action' => ''];
    }

    public function removeAction(int $index): void
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }

    public function addApproval(): void
    {
        $this->approvals[] = ['from' => '', 'to' => '', 'steps' => ''];
    }

    public function removeApproval(int $index): void
    {
        unset($this->approvals[$index]);
        $this->approvals = array_values($this->approvals);
    }

    public function save(): void
    {
        /** @var array{key: string, name?: string, nodes: list<array{id: string, key: string, initial?: bool}>, edges: list<array{from: string, to: string}>} $graph */
        $graph = $this->graph;

        app(WorkflowImporter::class)->import(GraphToBlueprint::convert($graph, $this->conditionMap(), $this->actionMap(), $this->approvalMap()));

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
     * @return array<string, string>
     */
    public function actionOptions(): array
    {
        $options = [];

        foreach (app(ActionCatalog::class)->names() as $name) {
            $options[$name] = $name;
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

    /**
     * @return array<string, list<string>>
     */
    private function actionMap(): array
    {
        $map = [];

        foreach ($this->actions as $rule) {
            if ($rule['from'] === '' || $rule['to'] === '' || $rule['action'] === '') {
                continue;
            }

            $map["{$rule['from']}>{$rule['to']}"][] = $rule['action'];
        }

        return $map;
    }

    /**
     * @return array<string, list<string>>
     */
    private function approvalMap(): array
    {
        $map = [];

        foreach ($this->approvals as $rule) {
            if ($rule['from'] === '' || $rule['to'] === '' || trim($rule['steps']) === '') {
                continue;
            }

            $steps = array_values(array_filter(array_map('trim', explode(',', $rule['steps']))));

            if ($steps !== []) {
                $map["{$rule['from']}>{$rule['to']}"] = $steps;
            }
        }

        return $map;
    }
}

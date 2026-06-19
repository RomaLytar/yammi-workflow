<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Yammi\Workflow\Application\Contract\ActionCatalog;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionOperator;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;
use Yammi\Workflow\Infrastructure\Designer\DesignerLoader;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowModel;

class WorkflowDesigner extends Page implements HasForms
{
    use InteractsWithForms;

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
            ['id' => 'n2', 'key' => 'pending', 'initial' => false],
            ['id' => 'n3', 'key' => 'approved', 'initial' => false],
        ],
        'edges' => [
            ['from' => 'n1', 'to' => 'n2'],
            ['from' => 'n2', 'to' => 'n3'],
        ],
    ];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['conditions' => [], 'actions' => [], 'approvals' => []]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Conditions')
                    ->description('A transition is allowed only when its rules pass — no code.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('conditions')
                            ->hiddenLabel()
                            ->columns(5)
                            ->addActionLabel('Add rule')
                            ->schema([
                                Select::make('from')->label('From')->options(fn (): array => $this->stateOptions()),
                                Select::make('to')->label('To')->options(fn (): array => $this->stateOptions()),
                                TextInput::make('field')->label('Field')->placeholder('amount'),
                                Select::make('operator')->label('Operator')->options($this->operatorOptions())->default('eq'),
                                TextInput::make('value')->label('Value')->placeholder('1000'),
                            ]),
                    ]),

                Section::make('Actions')
                    ->description('Run an app-registered action when a transition happens — no code.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('actions')
                            ->hiddenLabel()
                            ->columns(3)
                            ->addActionLabel('Add action')
                            ->schema([
                                Select::make('from')->label('From')->options(fn (): array => $this->stateOptions()),
                                Select::make('to')->label('To')->options(fn (): array => $this->stateOptions()),
                                Select::make('action')->label('Action')->options(fn (): array => $this->actionOptions())->placeholder('—'),
                            ]),
                    ]),

                Section::make('Approvals')
                    ->description('Require ordered sign-off before a transition — no code.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('approvals')
                            ->hiddenLabel()
                            ->columns(3)
                            ->addActionLabel('Add approval')
                            ->schema([
                                Select::make('from')->label('From')->options(fn (): array => $this->stateOptions()),
                                Select::make('to')->label('To')->options(fn (): array => $this->stateOptions()),
                                TextInput::make('steps')->label('Sign-off steps')->placeholder('manager, finance, ceo')->helperText('Comma-separated, in order'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('load')
                ->label('Load existing')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Select::make('key')->label('Workflow')->options(fn (): array => $this->workflowOptions())->required(),
                ])
                ->action(function (array $data): void {
                    $loaded = app(DesignerLoader::class)->load((string) $data['key']);

                    $this->graph = $this->toGraph($loaded);
                    $this->form->fill([
                        'conditions' => $loaded['conditions'],
                        'actions' => $loaded['actions'],
                        'approvals' => $loaded['approvals'],
                    ]);

                    $this->dispatch('graph-loaded');
                }),
        ];
    }

    public function save(): void
    {
        /** @var array<string, mixed> $rules */
        $rules = $this->form->getState();

        app(WorkflowImporter::class)->import($this->toBlueprint($this->graph, $rules));

        Notification::make()->title('Workflow saved as a new version')->success()->send();
    }

    /**
     * @param  array{key: string, name: string, states: list<array{key: string, initial: bool}>, transitions: list<array{from: string, to: string}>}  $loaded
     * @return array<string, mixed>
     */
    private function toGraph(array $loaded): array
    {
        $nodes = [];
        $idByKey = [];

        foreach ($loaded['states'] as $i => $state) {
            $id = 'n'.($i + 1);
            $nodes[] = ['id' => $id, 'key' => $state['key'], 'initial' => $state['initial']];
            $idByKey[$state['key']] = $id;
        }

        $edges = [];

        foreach ($loaded['transitions'] as $transition) {
            if (isset($idByKey[$transition['from']], $idByKey[$transition['to']])) {
                $edges[] = ['from' => $idByKey[$transition['from']], 'to' => $idByKey[$transition['to']]];
            }
        }

        return ['key' => $loaded['key'], 'name' => $loaded['name'], 'nodes' => $nodes, 'edges' => $edges];
    }

    /**
     * @param  array<string, mixed>  $graph
     * @param  array<string, mixed>  $rules
     */
    private function toBlueprint(array $graph, array $rules): WorkflowBlueprintData
    {
        $states = [];
        $initial = '';
        $keyById = [];

        foreach ($this->rows($graph, 'nodes') as $node) {
            $key = trim((string) ($node['key'] ?? ''));

            if ($key === '') {
                continue;
            }

            $states[] = $key;
            $keyById[(string) ($node['id'] ?? '')] = $key;

            if (($node['initial'] ?? false) && $initial === '') {
                $initial = $key;
            }
        }

        $transitions = [];

        foreach ($this->rows($graph, 'edges') as $edge) {
            $from = $keyById[(string) ($edge['from'] ?? '')] ?? '';
            $to = $keyById[(string) ($edge['to'] ?? '')] ?? '';

            if ($from !== '' && $to !== '') {
                $transitions[$from][] = $to;
            }
        }

        $conditions = [];

        foreach ($this->rows($rules, 'conditions') as $row) {
            if (($row['from'] ?? '') === '' || ($row['to'] ?? '') === '' || ($row['field'] ?? '') === '') {
                continue;
            }

            $conditions["{$row['from']}>{$row['to']}"][] = [
                'field' => (string) $row['field'],
                'operator' => (string) ($row['operator'] ?? 'eq'),
                'value' => (string) ($row['value'] ?? ''),
            ];
        }

        $actions = [];

        foreach ($this->rows($rules, 'actions') as $row) {
            if (($row['from'] ?? '') === '' || ($row['to'] ?? '') === '' || ($row['action'] ?? '') === '') {
                continue;
            }

            $actions["{$row['from']}>{$row['to']}"][] = (string) $row['action'];
        }

        $approval = [];

        foreach ($this->rows($rules, 'approvals') as $row) {
            $steps = array_values(array_filter(array_map('trim', explode(',', (string) ($row['steps'] ?? '')))));

            if (($row['from'] ?? '') !== '' && ($row['to'] ?? '') !== '' && $steps !== []) {
                $approval["{$row['from']}>{$row['to']}"] = $steps;
            }
        }

        $key = (string) ($graph['key'] ?? '');

        return new WorkflowBlueprintData(
            $key,
            (string) ($graph['name'] ?? '') ?: $key,
            $states,
            $transitions,
            $initial !== '' ? $initial : ($states[0] ?? ''),
            $conditions,
            $actions,
            $approval,
        );
    }

    /**
     * @param  array<string, mixed>  $bag
     * @return list<array<string, mixed>>
     */
    private function rows(array $bag, string $key): array
    {
        $rows = $bag[$key] ?? [];

        return is_array($rows) ? array_values($rows) : [];
    }

    /**
     * @return array<string, string>
     */
    private function stateOptions(): array
    {
        $options = [];

        foreach ($this->rows($this->graph, 'nodes') as $node) {
            $key = trim((string) ($node['key'] ?? ''));

            if ($key !== '') {
                $options[$key] = $key;
            }
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function operatorOptions(): array
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
    private function actionOptions(): array
    {
        $options = [];

        foreach (app(ActionCatalog::class)->names() as $name) {
            $options[$name] = $name;
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function workflowOptions(): array
    {
        /** @var array<string, string> $options */
        $options = WorkflowModel::query()
            ->where('is_current', true)
            ->orderBy('key')
            ->pluck('key', 'key')
            ->all();

        return $options;
    }
}

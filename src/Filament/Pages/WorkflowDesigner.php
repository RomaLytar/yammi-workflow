<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'key' => 'invoice',
            'name' => 'Invoice approval',
            'states' => [
                ['key' => 'draft', 'initial' => true],
                ['key' => 'pending', 'initial' => false],
                ['key' => 'approved', 'initial' => false],
            ],
            'transitions' => [
                ['from' => 'draft', 'to' => 'pending'],
                ['from' => 'pending', 'to' => 'approved'],
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Workflow')
                    ->description('A unique key and a human name for this process.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')->required()->helperText('Lower-case id, e.g. invoice'),
                        TextInput::make('name')->required(),
                    ]),

                Section::make('States')
                    ->description('The steps a record moves through. Mark exactly one as initial.')
                    ->schema([
                        Repeater::make('states')
                            ->hiddenLabel()
                            ->columns(2)
                            ->addActionLabel('Add state')
                            ->schema([
                                TextInput::make('key')->label('State')->placeholder('draft')->required()->live(onBlur: true),
                                Toggle::make('initial')->label('Initial state'),
                            ]),
                    ]),

                Section::make('Transitions')
                    ->description('Allowed moves between states. Anything not listed is blocked.')
                    ->schema([
                        Repeater::make('transitions')
                            ->hiddenLabel()
                            ->columns(2)
                            ->addActionLabel('Add transition')
                            ->schema([
                                Select::make('from')->label('From')->options(fn (): array => $this->stateOptions())->required()->searchable(),
                                Select::make('to')->label('To')->options(fn (): array => $this->stateOptions())->required()->searchable(),
                            ]),
                    ]),

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
                    $this->form->fill(app(DesignerLoader::class)->load((string) $data['key']));
                }),
        ];
    }

    public function save(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->form->getState();

        app(WorkflowImporter::class)->import($this->toBlueprint($data));

        Notification::make()->title('Workflow saved as a new version')->success()->send();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function toBlueprint(array $data): WorkflowBlueprintData
    {
        $states = [];
        $initial = '';

        foreach ($this->rows($data, 'states') as $state) {
            $key = trim((string) ($state['key'] ?? ''));

            if ($key === '') {
                continue;
            }

            $states[] = $key;

            if (($state['initial'] ?? false) && $initial === '') {
                $initial = $key;
            }
        }

        $transitions = [];

        foreach ($this->rows($data, 'transitions') as $row) {
            if (($row['from'] ?? '') !== '' && ($row['to'] ?? '') !== '') {
                $transitions[(string) $row['from']][] = (string) $row['to'];
            }
        }

        $conditions = [];

        foreach ($this->rows($data, 'conditions') as $row) {
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

        foreach ($this->rows($data, 'actions') as $row) {
            if (($row['from'] ?? '') === '' || ($row['to'] ?? '') === '' || ($row['action'] ?? '') === '') {
                continue;
            }

            $actions["{$row['from']}>{$row['to']}"][] = (string) $row['action'];
        }

        $approval = [];

        foreach ($this->rows($data, 'approvals') as $row) {
            $steps = array_values(array_filter(array_map('trim', explode(',', (string) ($row['steps'] ?? '')))));

            if (($row['from'] ?? '') !== '' && ($row['to'] ?? '') !== '' && $steps !== []) {
                $approval["{$row['from']}>{$row['to']}"] = $steps;
            }
        }

        $key = (string) ($data['key'] ?? '');

        return new WorkflowBlueprintData(
            $key,
            (string) ($data['name'] ?? '') ?: $key,
            $states,
            $transitions,
            $initial !== '' ? $initial : ($states[0] ?? ''),
            $conditions,
            $actions,
            $approval,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function rows(array $data, string $key): array
    {
        $rows = $data[$key] ?? [];

        return is_array($rows) ? array_values($rows) : [];
    }

    /**
     * @return array<string, string>
     */
    private function stateOptions(): array
    {
        $options = [];

        foreach ($this->rows($this->data ?? [], 'states') as $state) {
            $key = trim((string) ($state['key'] ?? ''));

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

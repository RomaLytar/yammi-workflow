<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Facade\Workflow;
use Yammi\Workflow\Filament\Resources\WorkflowInstanceResource\Pages\ListWorkflowInstances;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowInstanceModel;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowStateModel;

class WorkflowInstanceResource extends Resource
{
    protected static ?string $model = WorkflowInstanceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Workflow instances';

    protected static ?string $modelLabel = 'workflow instance';

    protected static ?string $pluralModelLabel = 'workflow instances';

    public static function table(Table $table): Table
    {
        $stateColors = [
            'draft' => 'gray',
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'paid' => 'success',
        ];

        return $table
            ->columns([
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(static fn (string $state): string => class_basename($state).' #'),
                TextColumn::make('subject_id')->label('')->grow(false),
                TextColumn::make('state')
                    ->label('State')
                    ->badge()
                    ->color(static fn (?string $state): string => $stateColors[$state] ?? 'gray')
                    ->getStateUsing(static fn (WorkflowInstanceModel $record): ?string => WorkflowStateModel::query()->whereKey($record->state_id)->value('key')),
                TextColumn::make('updated_at')->label('Updated')->since(),
            ])
            ->actions([
                Action::make('transition')
                    ->label('Transition')
                    ->icon('heroicon-o-arrow-right')
                    ->button()
                    ->form(static fn (WorkflowInstanceModel $record): array => [
                        Select::make('state')
                            ->label('Move to')
                            ->options(static function () use ($record): array {
                                $subject = self::subjectOf($record);
                                $targets = $subject !== null ? Workflow::allowedTransitions($subject) : [];

                                return array_combine($targets, $targets);
                            })
                            ->required(),
                    ])
                    ->action(static function (WorkflowInstanceModel $record, array $data): void {
                        $subject = self::subjectOf($record);

                        if ($subject !== null) {
                            Workflow::transition($subject, (string) $data['state']);
                        }
                    })
                    ->visible(static fn (WorkflowInstanceModel $record): bool => self::subjectOf($record) !== null
                        && Workflow::allowedTransitions(self::subjectOf($record)) !== []),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowInstances::route('/'),
        ];
    }

    private static function subjectOf(WorkflowInstanceModel $record): ?object
    {
        $class = $record->subject_type;

        if (! class_exists($class)) {
            return null;
        }

        $subject = $class::query()->find($record->subject_id);

        return $subject instanceof Model ? $subject : null;
    }
}

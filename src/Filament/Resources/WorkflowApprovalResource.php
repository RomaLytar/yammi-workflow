<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Resources;

use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Yammi\Workflow\Domain\Approval\Enum\ApprovalStatus;
use Yammi\Workflow\Facade\Approval;
use Yammi\Workflow\Filament\Resources\WorkflowApprovalResource\Pages\ListWorkflowApprovals;
use Yammi\Workflow\Infrastructure\Persistence\Eloquent\WorkflowApprovalModel;
use Yammi\Workflow\Infrastructure\Presentation\SubjectPresenter;

class WorkflowApprovalResource extends Resource
{
    protected static ?string $model = WorkflowApprovalModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Approvals';

    protected static ?string $modelLabel = 'approval';

    protected static ?string $pluralModelLabel = 'approvals';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label('Subject')
                    ->weight('bold')
                    ->state(static fn (WorkflowApprovalModel $record): string => SubjectPresenter::title($record->subject_type, $record->subject_id)),
                TextColumn::make('step')->label('Step')->sortable(),
                TextColumn::make('label')->label('Role'),
                TextColumn::make('assignee')
                    ->label('Assignee')
                    ->placeholder('—')
                    ->state(static fn (WorkflowApprovalModel $record): ?string => $record->assignee_type !== null && $record->assignee_id !== null
                        ? SubjectPresenter::title($record->assignee_type, $record->assignee_id)
                        : null),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(static fn (ApprovalStatus $state): string => ucfirst(strtolower($state->name)))
                    ->color(static fn (ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::Approved => 'success',
                        ApprovalStatus::Rejected => 'danger',
                        ApprovalStatus::Pending => 'warning',
                    }),
                TextColumn::make('comment')->limit(40)->placeholder('-'),
                TextColumn::make('decided_at')->dateTime()->since()->placeholder('-'),
            ])
            ->filters([
                Filter::make('mine')
                    ->label('Assigned to me')
                    ->query(static function (Builder $query): Builder {
                        $user = auth()->user();

                        return $user === null
                            ? $query
                            : $query->where('assignee_type', $user::class)->where('assignee_id', (string) $user->getAuthIdentifier());
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->button()
                    ->visible(static fn (WorkflowApprovalModel $record): bool => self::isCurrent($record))
                    ->form([Textarea::make('comment')->label('Comment')->rows(2)])
                    ->action(static fn (WorkflowApprovalModel $record, array $data) => self::decide($record, true, self::comment($data))),
                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->button()
                    ->visible(static fn (WorkflowApprovalModel $record): bool => self::isCurrent($record))
                    ->form([Textarea::make('comment')->label('Comment')->rows(2)])
                    ->action(static fn (WorkflowApprovalModel $record, array $data) => self::decide($record, false, self::comment($data))),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowApprovals::route('/'),
        ];
    }

    private static function isCurrent(WorkflowApprovalModel $record): bool
    {
        $subject = self::subjectOf($record);

        return $subject !== null && Approval::for($subject)->currentStep === $record->step;
    }

    private static function decide(WorkflowApprovalModel $record, bool $approve, ?string $comment): void
    {
        $subject = self::subjectOf($record);

        if ($subject === null) {
            return;
        }

        $approve
            ? Approval::approve($subject, $comment)
            : Approval::reject($subject, $comment);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function comment(array $data): ?string
    {
        $comment = $data['comment'] ?? null;

        return is_string($comment) && $comment !== '' ? $comment : null;
    }

    private static function subjectOf(WorkflowApprovalModel $record): ?object
    {
        return SubjectPresenter::resolve($record->subject_type, $record->subject_id);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
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

    public function save(): void
    {
        /** @var array{key: string, name?: string, nodes: list<array{id: string, key: string, initial?: bool}>, edges: list<array{from: string, to: string}>} $graph */
        $graph = $this->graph;

        app(WorkflowImporter::class)->import(GraphToBlueprint::convert($graph));

        Notification::make()
            ->title('Workflow saved as a new version')
            ->success()
            ->send();
    }
}

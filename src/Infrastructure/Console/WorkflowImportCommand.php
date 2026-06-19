<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Console;

use Illuminate\Console\Command;
use Yammi\Workflow\Application\DTO\WorkflowBlueprintData;
use Yammi\Workflow\Infrastructure\Definition\WorkflowImporter;

/**
 * @internal
 */
final class WorkflowImportCommand extends Command
{
    protected $signature = 'workflow:import {file}';

    protected $description = 'Import a workflow definition from a JSON file as a new version';

    public function __construct(
        private readonly WorkflowImporter $importer,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $argument = $this->argument('file');
        $file = is_string($argument) ? $argument : '';

        if (! is_file($file)) {
            $this->error("File \"{$file}\" not found.");

            return self::FAILURE;
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        if (! is_array($decoded) || ! isset($decoded['key'], $decoded['states'])) {
            $this->error('The file is not a valid workflow definition.');

            return self::FAILURE;
        }

        /** @var array{key: string, name?: string, states: list<string>, initial?: string, transitions?: array<string, list<string>>, conditions?: array<string, list<array{field: string, operator: string, value: string}>>, actions?: array<string, list<string>>, approval?: array<string, list<string>>} $decoded */
        $this->importer->import(WorkflowBlueprintData::fromArray($decoded));

        $this->info("Imported workflow \"{$decoded['key']}\" as a new version.");

        return self::SUCCESS;
    }
}

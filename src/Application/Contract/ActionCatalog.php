<?php

declare(strict_types=1);

namespace Yammi\Workflow\Application\Contract;

interface ActionCatalog
{
    public function run(string $name, object $subject): void;

    public function has(string $name): bool;

    /**
     * @return list<string>
     */
    public function names(): array;
}

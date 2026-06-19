<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Action;

use Yammi\Workflow\Application\Contract\ActionCatalog;

final class InMemoryActionCatalog implements ActionCatalog
{
    /** @var array<string, callable(object): void> */
    private array $actions = [];

    /**
     * @param  callable(object): void  $action
     */
    public function register(string $name, callable $action): void
    {
        $this->actions[$name] = $action;
    }

    public function run(string $name, object $subject): void
    {
        if (isset($this->actions[$name])) {
            ($this->actions[$name])($subject);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->actions[$name]);
    }

    public function names(): array
    {
        return array_keys($this->actions);
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\StateStore;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Yammi\Workflow\Application\Contract\StateStore;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

/**
 * @internal
 */
final class ColumnStateStore implements StateStore
{
    public function __construct(
        private readonly string $column,
    ) {}

    public function current(object $subject): ?State
    {
        $value = $this->model($subject)->getAttribute($this->column);

        if ($value === null || $value === '') {
            return null;
        }

        return new State((string) $value);
    }

    public function put(object $subject, State $state): void
    {
        $model = $this->model($subject);
        $model->setAttribute($this->column, $state->name);
        $model->save();
    }

    private function model(object $subject): Model
    {
        if (! $subject instanceof Model) {
            throw new InvalidArgumentException('The column state store requires an Eloquent model.');
        }

        return $subject;
    }
}

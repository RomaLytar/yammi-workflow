<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Presentation;

use Illuminate\Database\Eloquent\Model;
use Yammi\Workflow\Contracts\WorkflowSubject;

final class SubjectPresenter
{
    public static function resolve(string $type, string $id): ?Model
    {
        if (! class_exists($type) || ! is_a($type, Model::class, true)) {
            return null;
        }

        /** @var class-string<Model> $type */
        return $type::query()->find($id);
    }

    public static function title(string $type, string $id): string
    {
        $subject = self::resolve($type, $id);

        if ($subject instanceof WorkflowSubject) {
            return $subject->workflowTitle();
        }

        return class_basename($type).' #'.$id;
    }
}

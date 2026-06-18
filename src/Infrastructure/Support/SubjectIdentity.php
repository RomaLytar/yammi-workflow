<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * @internal
 */
final class SubjectIdentity
{
    public static function type(object $subject): string
    {
        return $subject instanceof Model ? $subject->getMorphClass() : $subject::class;
    }

    public static function id(object $subject): string
    {
        return $subject instanceof Model ? (string) $subject->getKey() : '';
    }
}

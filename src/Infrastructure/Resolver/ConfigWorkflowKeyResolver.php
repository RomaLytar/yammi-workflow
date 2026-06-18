<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Resolver;

use Yammi\Workflow\Application\Contract\WorkflowKeyResolver;
use Yammi\Workflow\Domain\Workflow\Exception\SubjectNotMappedException;

/**
 * @internal
 */
final class ConfigWorkflowKeyResolver implements WorkflowKeyResolver
{
    /**
     * @param  array<string, string>  $map
     */
    public function __construct(
        private readonly array $map,
    ) {}

    public function keyFor(object $subject): string
    {
        $class = $subject::class;

        return $this->map[$class] ?? throw SubjectNotMappedException::forClass($class);
    }
}

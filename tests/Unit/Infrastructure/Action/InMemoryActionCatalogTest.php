<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Infrastructure\Action;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yammi\Workflow\Infrastructure\Action\InMemoryActionCatalog;

final class InMemoryActionCatalogTest extends TestCase
{
    public function test_it_registers_runs_and_lists_actions(): void
    {
        $catalog = new InMemoryActionCatalog;
        $ran = [];

        $catalog->register('notify', static function (object $subject) use (&$ran): void {
            $ran[] = $subject;
        });

        $this->assertTrue($catalog->has('notify'));
        $this->assertFalse($catalog->has('missing'));
        $this->assertSame(['notify'], $catalog->names());

        $subject = new stdClass;
        $catalog->run('notify', $subject);

        $this->assertSame([$subject], $ran);
    }

    public function test_running_an_unknown_action_is_a_no_op(): void
    {
        $catalog = new InMemoryActionCatalog;

        $catalog->run('ghost', new stdClass);

        $this->assertSame([], $catalog->names());
    }
}

<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Domain\Workflow\ValueObject;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Domain\Workflow\Exception\InvalidStateException;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;

final class StateTest extends TestCase
{
    public function test_it_exposes_its_name(): void
    {
        $this->assertSame('draft', (new State('draft'))->name);
    }

    public function test_it_trims_surrounding_whitespace(): void
    {
        $this->assertSame('draft', (new State("  draft\n"))->name);
    }

    public function test_it_rejects_an_empty_name(): void
    {
        $this->expectException(InvalidStateException::class);

        new State('');
    }

    public function test_it_rejects_a_whitespace_only_name(): void
    {
        $this->expectException(InvalidStateException::class);

        new State('   ');
    }

    public function test_equality_is_by_name(): void
    {
        $this->assertTrue((new State('draft'))->equals(new State('draft')));
        $this->assertFalse((new State('draft'))->equals(new State('approved')));
    }

    public function test_it_casts_to_string(): void
    {
        $this->assertSame('approved', (string) new State('approved'));
    }
}

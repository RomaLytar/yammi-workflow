<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Domain\Workflow\ValueObject;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Domain\Workflow\ValueObject\State;
use Yammi\Workflow\Domain\Workflow\ValueObject\Transition;

final class TransitionTest extends TestCase
{
    public function test_it_matches_its_endpoints(): void
    {
        $transition = new Transition(new State('draft'), new State('pending'));

        $this->assertTrue($transition->matches(new State('draft'), new State('pending')));
    }

    public function test_it_does_not_match_different_endpoints(): void
    {
        $transition = new Transition(new State('draft'), new State('pending'));

        $this->assertFalse($transition->matches(new State('draft'), new State('approved')));
        $this->assertFalse($transition->matches(new State('pending'), new State('pending')));
    }

    public function test_it_reports_its_origin(): void
    {
        $transition = new Transition(new State('draft'), new State('pending'));

        $this->assertTrue($transition->startsFrom(new State('draft')));
        $this->assertFalse($transition->startsFrom(new State('pending')));
    }
}

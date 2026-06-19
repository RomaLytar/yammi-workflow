<?php

declare(strict_types=1);

namespace Yammi\Workflow\Tests\Unit\Domain\Workflow\Condition;

use PHPUnit\Framework\TestCase;
use Yammi\Workflow\Domain\Workflow\Condition\Condition;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionOperator;
use Yammi\Workflow\Domain\Workflow\Condition\ConditionSet;

final class ConditionTest extends TestCase
{
    public function test_equality_operators(): void
    {
        $this->assertTrue(ConditionOperator::Equals->matches('high', 'high'));
        $this->assertFalse(ConditionOperator::Equals->matches('low', 'high'));
        $this->assertTrue(ConditionOperator::NotEquals->matches('low', 'high'));
    }

    public function test_numeric_operators(): void
    {
        $this->assertTrue(ConditionOperator::GreaterThan->matches(2000, '1000'));
        $this->assertFalse(ConditionOperator::GreaterThan->matches(500, '1000'));
        $this->assertTrue(ConditionOperator::GreaterOrEqual->matches(1000, '1000'));
        $this->assertTrue(ConditionOperator::LessThan->matches(500, '1000'));
        $this->assertTrue(ConditionOperator::LessOrEqual->matches(1000, '1000'));
    }

    public function test_in_and_emptiness_operators(): void
    {
        $this->assertTrue(ConditionOperator::In->matches('b', 'a, b, c'));
        $this->assertFalse(ConditionOperator::In->matches('z', 'a,b'));
        $this->assertTrue(ConditionOperator::IsEmpty->matches(null, ''));
        $this->assertTrue(ConditionOperator::NotEmpty->matches('x', ''));
        $this->assertFalse(ConditionOperator::NotEmpty->matches('', ''));
    }

    public function test_boolean_is_normalised(): void
    {
        $this->assertTrue(ConditionOperator::Equals->matches(true, '1'));
        $this->assertTrue(ConditionOperator::Equals->matches(false, '0'));
    }

    public function test_condition_evaluates_a_value(): void
    {
        $condition = new Condition('amount', ConditionOperator::GreaterOrEqual, '1000');

        $this->assertTrue($condition->matches(1500));
        $this->assertFalse($condition->matches(500));
    }

    public function test_condition_set_requires_every_rule(): void
    {
        $set = new ConditionSet([
            new Condition('amount', ConditionOperator::GreaterOrEqual, '1000'),
            new Condition('vip', ConditionOperator::Equals, '1'),
        ]);

        $this->assertTrue($set->satisfiedBy(['amount' => 2000, 'vip' => '1']));
        $this->assertFalse($set->satisfiedBy(['amount' => 2000, 'vip' => '0']));
        $this->assertTrue((new ConditionSet([]))->isEmpty());
    }
}

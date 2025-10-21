<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\Rule\NullRule;
use PHPUnit\Framework\TestCase;

class NullRuleTest extends TestCase
{
    public function testApply(): void
    {
        $rule = new NullRule();
        $this->assertSame('value', $rule->apply('test', 'value'));
    }
}

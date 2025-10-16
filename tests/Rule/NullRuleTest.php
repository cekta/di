<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\Rule\NullRule;
use PHPUnit\Framework\TestCase;

class NullRuleTest extends TestCase
{
    public function testApply()
    {
        $rule = new NullRule();
        $this->assertSame(['dep1', 'dep2'], $rule->apply('test', ['dep1', 'dep2']));
    }
}

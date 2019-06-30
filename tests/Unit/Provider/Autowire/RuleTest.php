<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire;

use Cekta\DI\Provider\Autowire\Rule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{

    public function testAcceptance()
    {
        $rule = new Rule('abc', ['test' => 'value']);
        static::assertTrue($rule->acceptable('abc'));
        static::assertTrue($rule->acceptable('abcd'));
        static::assertFalse($rule->acceptable('ab'));
    }

    public function testAccept()
    {
        $rule = new Rule('abc', ['test' => 'value']);
        static::assertSame(['test' => 'value'], $rule->accept());
    }
}

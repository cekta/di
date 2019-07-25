<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowiring;

use Cekta\DI\Provider\Autowiring\Rule;
use Cekta\DI\Provider\Autowiring\RuleInterface;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    public function testRuleMustImplementRuleInterface(): void
    {
        $rule = new Rule('', ['test' => 'value']);
        static::assertInstanceOf(RuleInterface::class, $rule);
    }

    public function testAcceptance(): void
    {
        $rule = new Rule('abc', ['test' => 'value']);
        static::assertTrue($rule->acceptable('abc'));
        static::assertTrue($rule->acceptable('abcd'));
        static::assertFalse($rule->acceptable('ab'));
    }

    public function testAccept(): void
    {
        $rule = new Rule('abc', ['test' => 'value']);
        static::assertSame(['test' => 'value'], $rule->accept());
    }
}

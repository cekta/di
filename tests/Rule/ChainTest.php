<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\Rule;
use Cekta\DI\Rule\Chain;
use PHPUnit\Framework\TestCase;

class ChainTest extends TestCase
{
    public function testApply(): void
    {
        $dependency_name = 'value';

        $mock1 = $this->createMock(Rule::class);
        $mock1->expects($this->once())
            ->method('apply')
            ->willReturn($dependency_name);

        $mock2 = $this->createMock(Rule::class);
        $mock2->expects($this->once())
            ->method('apply')
            ->willReturn($dependency_name);

        $rule = new Chain($mock1, $mock2);

        $this->assertEquals(
            $dependency_name,
            $rule->apply('test', $dependency_name)
        );
    }
}

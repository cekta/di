<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\DependencyDTO;
use Cekta\DI\Rule;
use Cekta\DI\Rule\Chain;
use PHPUnit\Framework\TestCase;

class ChainTest extends TestCase
{
    public function testApply(): void
    {
        $dependencies = [new DependencyDTO('dep1'), new DependencyDTO('dep2')];

        $mock1 = $this->createMock(Rule::class);
        $mock1->expects($this->once())
            ->method('apply')
            ->willReturn($dependencies);

        $mock2 = $this->createMock(Rule::class);
        $mock2->expects($this->once())
            ->method('apply')
            ->willReturn($dependencies);

        $rule = new Chain($mock1, $mock2);

        $this->assertEquals($dependencies, $rule->apply('test', $dependencies));
    }
}

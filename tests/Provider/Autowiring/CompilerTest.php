<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider\Autowiring;

use Cekta\DI\Provider\Autowiring\Compiler;
use Cekta\DI\Provider\Autowiring\Reflection;
use Cekta\DI\Provider\Autowiring\ReflectionClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $reflection;
    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp(): void
    {
        $this->reflection = $this->createMock(Reflection::class);
        assert($this->reflection instanceof Reflection);
        $this->compiler = new Compiler($this->reflection);
    }

    public function testCompileWithoutArgument(): void
    {
        $a = $this->createMock(ReflectionClass::class);
        $a->method('getDependencies')->willReturn(['b', 'c']);
        $a->method('isInstantiable')->willReturn(true);
        $b = $this->createMock(ReflectionClass::class);
        $b->expects($this->never())->method('getDependencies');
        $b->method('isInstantiable')->willReturn(false);
        $c = $this->createMock(ReflectionClass::class);
        $c->method('getDependencies')->willReturn(['d']);
        $c->method('isInstantiable')->willReturn(true);
        $d = $this->createMock(ReflectionClass::class);
        $d->method('getDependencies')->willReturn([]);
        $d->method('isInstantiable')->willReturn(true);
        $this->reflection->method('getClass')->willReturnMap(
            [
                ['a', $a],
                ['b', $b],
                ['c', $c],
                ['d', $d]
            ]
        );
        $expected = <<<'TAG'
[
    'a' => function(\Psr\Container\ContainerInterface $container) {
        return new a($container['b'], $container['c']);
    },
    'c' => function(\Psr\Container\ContainerInterface $container) {
        return new c($container['d']);
    },
    'd' => function(\Psr\Container\ContainerInterface $container) {
        return new d();
    },
];
TAG;
        $this->assertSame($expected, $this->compiler->compile(['a']));
    }
}

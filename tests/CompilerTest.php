<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Provider\Autowiring\Reflection;
use Cekta\DI\Provider\Autowiring\ReflectionClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

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

    /**
     * @throws ReflectionException
     */
    public function testCompileWithoutArgument(): void
    {
        $reflection = $this->createMock(ReflectionClass::class);
        $reflection->method('getDependencies')->willReturn([]);
        $this->reflection->method('getClass')->with('test')->willReturn($reflection);
        $expected = <<<'TAG'
[
    'test' => function(\Psr\Container\ContainerInterface $container) {
        return new test();
    }
];
TAG;
        $this->assertSame($expected, $this->compiler->compile(['test']));
    }
}

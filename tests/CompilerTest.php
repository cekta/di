<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\CompilerTest\Example;
use Cekta\DI\Test\CompilerTest\ExampleWithParams;
use Iterator;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    private Compiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new Compiler();
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     */
    public function testCompileWithoutNamespace(): void
    {
        $code = $this->compiler->compile(fqcn: 'Container');
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('namespace', $code);
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     */
    public function testCompileWithoutRequiredParams(): void
    {
        $this->expectException(InvalidContainerForCompile::class);
        $this->expectExceptionMessage(
            sprintf(
                'Invalid container:`%s` for compile, stack: %s',
                'password',
                implode(', ', [ExampleWithParams::class])
            ),
        );

        $this->compiler->compile(
            containers: [
                ExampleWithParams::class
            ],
            params: ['username' => 'value username']
        );
    }

    /**
     * @throws InvalidContainerForCompile
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     */
    public function testCompileNotInstantiable(): void
    {
        $name = Iterator::class;
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage("`$name` must be instantiable");

        $this->compiler->compile(
            containers: [
                Example::class
            ],
        );
    }
}

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

    /**
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable
     * @throws InfiniteRecursion
     */
    public function testCompileWithoutNamespace(): void
    {
        $compiler = new Compiler(fqcn: 'Container');
        $code = $compiler->compile();
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

        (new Compiler(
            containers: [
                ExampleWithParams::class
            ],
            params: ['username' => 'value username']
        ))->compile();
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

        (new Compiler(
            containers: [
                Example::class
            ],
        ))->compile();
    }
}

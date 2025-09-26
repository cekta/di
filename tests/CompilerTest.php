<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\Fixture\B;
use Cekta\DI\Test\Fixture\D;
use Cekta\DI\Test\Fixture\I;
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
                implode(', ', [B::class])
            ),
        );

        $this->compiler->compile(
            containers: [
                B::class
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
        $name = I::class;
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage("`$name` must be instantiable");

        $this->compiler->compile(
            containers: [
                D::class
            ],
        );
    }
}

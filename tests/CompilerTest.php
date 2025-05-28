<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\Fixture\B;
use Cekta\DI\Test\Fixture\D;
use Cekta\DI\Test\Fixture\I;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CompilerTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws NotInstantiable
     */
    public function testCompileWithoutNamespace(): void
    {
        $code = (new Compiler(fqcn: 'Container'))->__toString();
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('namespace', $code);
    }

    /**
     * @throws NotInstantiable
     */
    public function testCompileWithoutRequiredParams(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class "password" does not exist');

        (new Compiler(
            containers: [
                B::class
            ],
            params: ['username' => 'value username']
        ))->__toString();
    }

    /**
     * @throws ReflectionException
     */
    public function testCompileNotInstantiable(): void
    {
        $name = I::class;
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage("`$name` must be instantiable");

        (new Compiler(
            containers: [
                D::class
            ],
        ))->__toString();
    }
}

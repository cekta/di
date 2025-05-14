<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Test\Fixture\B;
use Cekta\DI\Test\Fixture\D;
use Cekta\DI\Test\Fixture\I;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;

class CompilerTest extends TestCase
{
    public function testCompileWithoutNamespace(): void
    {
        $code = (new Compiler(fqcn: 'Container'))->__toString();
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('namespace', $code);
    }

    public function testCompileWithoutRequiredParams(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class "password" does not exist');
        $this->expectExceptionCode(1);

        (new Compiler(
            params: ['username' => 'value username'],
            containers: [
                B::class
            ]
        ))->__toString();
    }

    public function testCompileNotInstantiable(): void
    {
        $name = I::class;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("`{$name}` must be instantiable");
        $this->expectExceptionCode(2);

        (new Compiler(
            containers: [
                D::class
            ],
        ))->__toString();
    }
}

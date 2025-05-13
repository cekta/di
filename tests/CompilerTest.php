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
        $code = (new Compiler(fqcn: 'Container'))->compile();
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('namespace', $code);
    }

    public function testCompileWithoutRequiredParams(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class "password" does not exist');

        (new Compiler(
            containers: [
                B::class
            ],
            params: ['username' => 'value username']
        ))->compile();
    }

    public function testCompileNotInstantiable(): void
    {
        $name = I::class;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("`{$name}` must be instantiable");

        (new Compiler(
            containers: [
                D::class
            ],
        ))->compile();
    }
}

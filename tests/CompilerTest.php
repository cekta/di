<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\CompilerTest\Example;
use Iterator;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testCompileWithoutNamespace(): void
    {
        $compiler = new Compiler(fqcn: 'Container');
        $code = $compiler->compile();
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('namespace', $code);
    }

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

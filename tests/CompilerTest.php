<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Configuration;
use Cekta\DI\Exception\NotFoundOnCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\CompilerTest\Example;
use Iterator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testCompileWithoutNamespace(): void
    {
        $compiler = new Configuration(fqcn: 'Container');
        $code = $compiler->compile();
        Assert::assertNotEmpty($code);
        Assert::assertStringNotContainsString('namespace', $code);
    }

    public function testCompileNotInstantiable(): void
    {
        $name = Iterator::class;
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage("`$name` not instantiable");

        (new Configuration(
            containers: [
                Example::class
            ],
        ))->compile();
    }

    public function testNotFoundOnCompile(): void
    {
        $container = 'invalid name';
        $this->expectException(NotFoundOnCompile::class);
        $this->expectExceptionMessage("`$container` not found on compile, stack: $container");

        (new Configuration(containers: [$container]))->compile();
    }
}

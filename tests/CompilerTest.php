<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\CompilerTest\EntrypointBugOfAlias;
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

    /**
     * @return void
     * @see https://github.com/cekta/di/issues/146
     */
    public function testParamMaxPriority(): void
    {
        $compiler = new Compiler(
            containers: [EntrypointBugOfAlias::class],
            params: [
                'some_argument_name' => 'value from params',
            ],
            alias: [
                'some_argument_name' => 'invalid name',
            ],
        );
        $this->assertIsString($compiler->compile(), 'must be success compiled');
    }
}

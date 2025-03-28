<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    private Compiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new Compiler();
    }

    public function testCompileInvalidFQCN(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->compiler->compile(fqcn: 'invalid fqcn');
    }

    public function testCompileWithoutNamespace(): void
    {
        $code = $this->compiler->compile(fqcn: '\Container');
        $this->assertNotEmpty($code);
        $this->assertStringNotContainsString('namespace', $code);
    }
}

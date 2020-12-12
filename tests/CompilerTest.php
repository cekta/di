<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Container\Compiled\Factory;
use Cekta\DI\Reflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $reflection;

    protected function setUp(): void
    {
        $this->reflection = $this->createMock(Reflection::class);
    }

    public function testCompile(): void
    {
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->reflection->method('getDependencies')->willReturnMap(
            [
                ['a', []],
                ['b', ['c', 'd']]
            ]
        );
        assert($this->reflection instanceof Reflection);
        $compile = new Compiler($this->reflection);
        $factory = Factory::class;
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

return [
'a' => new $factory('a', ...array (
)),

'b' => new $factory('b', ...array (
  0 => 'c',
  1 => 'd',
)),
];
COMPILED;

        $this->assertSame($expected, $compile->compile(...['a', 'b']));
    }

    public function testCompileNotInstantiable(): void
    {
        $this->reflection->method('isInstantiable')->willReturn(false);
        assert($this->reflection instanceof Reflection);
        $compile = new Compiler($this->reflection);
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

return [];
COMPILED;

        $this->assertSame($expected, $compile->compile(...['a', 'b']));
    }
}

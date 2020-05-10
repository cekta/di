<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;
use Cekta\DI\Reflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $reflection;
    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp(): void
    {
        $this->reflection = $this->createMock(Reflection::class);
        assert($this->reflection instanceof Reflection);
        $this->compiler = new Compiler($this->reflection);
    }

    public function testAutowiringWithArguments(): void
    {
        $this->reflection
            ->method('getDependencies')
            ->willReturnMap(
                [
                    ['test2', ['a', 'b']],
                    ['test3', []]
                ]
            );
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->compiler->autowire('test2');
        $this->compiler->autowire('test3');
        $class = Factory::class;
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

return [
    'test2' => new $class(
        'test2',
        ...array (
          0 => 'a',
          1 => 'b',
        )
    ),
    'test3' => new $class(
        'test3',
        ...array (
        )
    ),
];
COMPILED;
        $this->assertSame($expected, $this->compiler->compile());
    }

    public function testAutowiringWithVariadic(): void
    {
        $this->reflection->method('getDependencies')->willReturn(['a', 'b']);
        $this->reflection->method('isVariadic')->willReturn(true);
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->compiler->autowire('test2');
        $class = FactoryVariadic::class;
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

return [
    'test2' => new $class(
        'test2',
        ...array (
          0 => 'a',
          1 => 'b',
        )
    ),
];
COMPILED;
        $this->assertSame($expected, $this->compiler->compile());
    }

    public function testAutowiringNotInstantiable()
    {
        $name = 'test';
        $this->reflection->method('isInstantiable')->with($name)->willReturn(false);
        $this->compiler->autowire($name);
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

return [
];
COMPILED;
        $this->assertSame($expected, $this->compiler->compile());
    }
}

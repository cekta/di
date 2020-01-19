<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Provider\Autowiring\Reflection;
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

    public function testRegisterClass(): void
    {
        $this->compiler->registerClass('test', []);
        $this->compiler->registerClass('test2', ['a', 'b']);
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

use \Cekta\DI\Loader\Alias;
use \Cekta\DI\Loader\Factor;

return [
    'test' => new Factory('test'),
    'test2' => new Factory('test2', 'a', 'b'),
];
COMPILED;
        $this->assertSame($expected, $this->compiler->compile());
    }

    public function testRegisterInterface(): void
    {
        $this->compiler->registerInterface('test', 'value');
        $this->compiler->registerInterface('test2', 'value2');
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

use \Cekta\DI\Loader\Alias;
use \Cekta\DI\Loader\Factor;

return [
    'test' => new Alias('value'),
    'test2' => new Alias('value2'),
];
COMPILED;
        $this->assertSame($expected, $this->compiler->compile());
    }

    public function testAutowiringWithArguments(): void
    {
        $this->reflection
            ->method('getDependencies')
            ->with('test2')
            ->willReturn(['a', 'b']);
        $this->compiler->autowire('test2');
        $expected = <<<"COMPILED"
<?php

declare(strict_types=1);

use \Cekta\DI\Loader\Alias;
use \Cekta\DI\Loader\Factor;

return [
    'test2' => new Factory('test2', 'a', 'b'),
];
COMPILED;
        $this->assertSame($expected, $this->compiler->compile());
    }
}

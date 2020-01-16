<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Loader\Alias;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

class KeyValueTest extends TestCase
{
    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, new KeyValue([]));
    }

    public function testCanProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertTrue($provider->canProvide('key'));
        static::assertFalse($provider->canProvide('invalid name'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertEquals('value', $provider->provide('key'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideNotFound(): void
    {
        $this->expectException(NotFound::class);
        (new KeyValue([]))->provide('magic');
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testStringToAlias(): void
    {
        $provider = KeyValue::stringToAlias(
            [
                'a' => stdClass::class,
                'b' => 123
            ]
        );
        $this->assertSame(123, $provider->provide('b'));
        $this->assertInstanceOf(Alias::class, $provider->provide('a'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testStringToType(): void
    {
        $provider = KeyValue::stringToType(
            [
                'a' => 123,
                'b' => 'true',
                'b2' => '(true)',
                'c' => 'false',
                'c2' => '(false)',
                'e' => 'empty',
                'e2' => '(empty)',
                'n' => 'null',
                'n2' => '(null)',
                'r' => '"test"',
                'r2' => "'test'",
                'REGISTER' => 'True'
            ]
        );
        $this->assertSame(123, $provider->provide('a'));
        $this->assertTrue($provider->provide('b'));
        $this->assertTrue($provider->provide('b2'));
        $this->assertTrue($provider->provide('REGISTER'));
        $this->assertFalse($provider->provide('c'));
        $this->assertFalse($provider->provide('c2'));
        $this->assertEmpty($provider->provide('e'));
        $this->assertEmpty($provider->provide('e2'));
        $this->assertNull($provider->provide('n'));
        $this->assertNull($provider->provide('n2'));
        $this->assertSame('test', $provider->provide('r'));
        $this->assertSame('test', $provider->provide('r2'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testCompile(): string
    {
        $compiledFile = __DIR__ . '/compiled.php';
        $provider = KeyValue::compile(
            $compiledFile,
            function () {
                return <<<"COMPILED"
<?php

return ['test' => 'compiled content'];
COMPILED;
            }
        );
        $this->assertSame('compiled content', $provider->provide('test'));
        return $compiledFile;
    }

    /**
     * @depends testCompile
     * @param string $compiledFile
     * @throws ProviderExceptionInterface
     */
    public function testCompileWithCompiledFile(string $compiledFile): void
    {
        $provider = KeyValue::compile(
            $compiledFile,
            function () {
                throw new LogicException("function not be called, compiled file exists");
            }
        );
        $this->assertSame('compiled content', $provider->provide('test'));
        unlink($compiledFile);
    }

    public function testCompileNotWritable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`/notWritable` must be writable');
        KeyValue::compile(
            '/notWritable',
            function () {
            }
        );
    }
}

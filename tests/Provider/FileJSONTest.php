<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\FileJSON;
use Cekta\DI\ProviderException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileJSONTest extends TestCase
{
    /**
     * @var FileJSON
     */
    private static $provider;
    private static $path = __DIR__ . '/test.json';

    protected function setUp(): void
    {
        parent::setUp();
        self::$provider = new FileJSON(self::$path);
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        file_put_contents(
            self::$path,
            (string)json_encode(
                [
                    "string" => 'string value',
                    'int' => 123,
                    'bool' => false,
                    'float' => 0.5,
                    'null' => null,
                    'array' => ['some array element'],
                    'obj' => [
                        ['key' => 'value of object by key']
                    ]
                ]
            )
        );
        self::$provider = new FileJSON(self::$path);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        unlink(self::$path);
    }

    public function testCanProvide()
    {
        $this->assertTrue(self::$provider->canProvide('string'));
        $this->assertTrue(self::$provider->canProvide('int'));
        $this->assertTrue(self::$provider->canProvide('bool'));
        $this->assertTrue(self::$provider->canProvide('float'));
        $this->assertTrue(self::$provider->canProvide('obj'));
        $this->assertTrue(self::$provider->canProvide('null'));
    }

    public function testCanProvideInvalideName()
    {
        $this->assertFalse(self::$provider->canProvide('invalide name'));
    }

    /**
     * @throws ProviderException
     */
    public function testProvide()
    {
        $this->assertSame('string value', self::$provider->provide('string'));
        $this->assertSame(123, self::$provider->provide('int'));
        $this->assertSame(null, self::$provider->provide('null'));
        $this->assertSame(false, self::$provider->provide('bool'));
        $this->assertSame(0.5, self::$provider->provide('float'));
        $this->assertSame(['some array element'], self::$provider->provide('array'));
        $this->assertSame([['key' => 'value of object by key']], self::$provider->provide('obj'));
    }

    public function testInvalidPath()
    {
        $path = 'badfile';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("invalid path: `$path`");
        new FileJSON($path);
    }
}

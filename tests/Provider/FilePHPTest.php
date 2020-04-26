<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\FilePHP;
use Cekta\DI\ProviderException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FilePHPTest extends TestCase
{
    private static $path = __DIR__ . '/test.php';
    /**
     * @var FilePHP
     */
    private $provider;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        file_put_contents(
            self::$path,
            <<<'PHP'
<?php

return [
    'key' => 'value'
];
PHP
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new FilePHP(self::$path);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        unlink(self::$path);
    }

    /**
     * @throws ProviderException
     */
    public function testProvide()
    {
        $this->assertSame('value', $this->provider->provide('key'));
    }

    public function testCanProvide()
    {
        $this->assertTrue($this->provider->canProvide('key'));
        $this->assertFalse($this->provider->canProvide('invalid name'));
    }

    public function testInvalidPath()
    {
        $path = 'badfile';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("invalid path: `$path`");
        new FilePHP($path);
    }
}

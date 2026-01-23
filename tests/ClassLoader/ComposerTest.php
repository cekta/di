<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ClassLoader;

use Cekta\DI\ClassLoader\Composer;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class ComposerTest extends TestCase
{
    public function testEmpty(): void
    {
        $loader = new Composer(__DIR__ . '/Composer/empty.php');
        Assert::assertEmpty(iterator_to_array($loader()));
    }

    public function testExample(): void
    {
        $loader = new Composer(__DIR__ . '/Composer/example.php');
        /** @var ReflectionClass<object>[] $result */
        $result = iterator_to_array($loader());
        Assert::assertSame(stdClass::class, $result[0]->name);
        Assert::assertSame(Iterator::class, $result[1]->name);
    }

    /**
     * some library can drop exception on use deprecated
     * @see https://github.com/Nyholm/psr7/blob/889d890ba25e4264ad92ce56b18ef8218611ca51/src/Factory/HttplugFactory.php#L15
     * @return void
     */
    public function testExampleWithIgnore()
    {
        $loader = new Composer(__DIR__ . '/Composer/example.php', [Iterator::class]);
        /** @var ReflectionClass<object>[] $result */
        $result = iterator_to_array($loader());
        Assert::assertSame(stdClass::class, $result[0]->name);
        Assert::assertCount(1, $result);
    }

    public function testWithoutReturn(): void
    {
        $filename = __DIR__ . '/Composer/without_return.php';
        $loader = new Composer($filename);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("`$filename` must return array");
        iterator_to_array($loader());
    }

    /**
     * when container deleted after dump autoload optimize,
     * container cant load Container class because registered class but file not found (warning in composer ClassLoader)
     * @return void
     */
    public function testWithDeletedFilesClass(): void
    {
        $loader = new Composer(__DIR__ . '/Composer/with_deleted_files.php');
        /** @var ReflectionClass<object>[] $result */
        $result = iterator_to_array($loader());
        Assert::assertSame(stdClass::class, $result[0]->name);
        Assert::assertCount(1, $result);
    }
}

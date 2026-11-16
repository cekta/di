<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerFactory;
use Cekta\DI\Manifest;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ContainerFactoryTest extends TestCase
{
    private const FQCN = 'Cekta\DI\Test\ContainerFactoryTestContainer';
    private const FILENAME = __DIR__ . '/ContainerFactoryTestContainer.php';

    public function setUp(): void
    {
        $this->cleanup();
    }

    public function tearDown(): void
    {
        $this->cleanup();
    }

    private function cleanup(): void
    {
        if (file_exists(self::FILENAME)) {
            unlink(self::FILENAME);
        }
    }

    public function testCreateDefaultContainer(): void
    {
        $manifest = new Manifest(
            filename: self::FILENAME,
            fqcn: self::FQCN,
        );

        $isUnlinkCalled = false;
        $isFileExistCalled = false;
        $isFilePutContentsCalled = false;
        $factory = new ContainerFactory(
            unlink: function () use (&$isUnlinkCalled) {
                $isUnlinkCalled = true;
            },
            fileExists: function (string $filename) use (&$isFileExistCalled) {
                $isFileExistCalled = true;
                return file_exists($filename);
            },
            filePutContents: function (string $filename, mixed $data) use (&$isFilePutContentsCalled) {
                $isFilePutContentsCalled = true;
                return file_put_contents($filename, $data);
            },
        );
        $container = $factory->build($manifest);
        Assert::assertFalse($isUnlinkCalled, 'unlink must never call');
        Assert::assertTrue($isFileExistCalled, 'file_exists must be called');
        Assert::assertTrue($isFilePutContentsCalled, 'file_put_contents must be called at first time call');
        Assert::assertInstanceOf(\Psr\Container\ContainerInterface::class, $container);
        Assert::assertTrue(file_exists(self::FILENAME));

        $isUnlinkCalled = false;
        $isFileExistCalled = false;
        $isFilePutContentsCalled = false;
        $container = $factory->build($manifest);
        // @phpstan-ignore staticMethod.impossibleType
        Assert::assertFalse($isUnlinkCalled, 'unlink must never call');
        // @phpstan-ignore staticMethod.impossibleType
        Assert::assertTrue($isFileExistCalled, 'file_exists must be called');
        // @phpstan-ignore staticMethod.impossibleType
        Assert::assertFalse($isFilePutContentsCalled, 'file_put_contents not called at second time');
        Assert::assertInstanceOf(\Psr\Container\ContainerInterface::class, $container);
        Assert::assertTrue(file_exists(self::FILENAME));
    }

    public function testForceRecreate(): void
    {
        $manifest = new Manifest(
            filename: self::FILENAME,
            fqcn: self::FQCN,
            params: [],
            definition: [
                'entries' => [],
            ],
        );
        touch($manifest->filename());
        $isUnlinkCalled = false;
        $factory = new ContainerFactory(
            unlink: function ($filename) use (&$isUnlinkCalled) {
                $isUnlinkCalled = true;
                return unlink($filename);
            },
        );
        $container = $factory->build(manifest: $manifest, forceRecreate: true);

        Assert::assertTrue($isUnlinkCalled, 'unlink must be called');
        Assert::assertInstanceOf(
            \Psr\Container\ContainerInterface::class,
            $container,
            'result must implement ContainerInterface'
        );
        Assert::assertTrue(file_exists(self::FILENAME));
    }
}

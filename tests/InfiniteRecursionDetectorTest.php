<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\InfiniteRecursionDetectorTest\A;
use Cekta\DI\Test\InfiniteRecursionDetectorTest\B;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class InfiniteRecursionDetectorTest extends TestCase
{
    private const FILE = __DIR__ . '/ContainerInfiniteRecursion.php';
    private const FQCN = 'Cekta\DI\Test\ContainerInfiniteRecursion';

    protected function tearDown(): void
    {
        if (file_exists($this::FILE)) {
            unlink(self::FILE);
        }
    }

    /**
     * @throws IOExceptionInterface
     * @throws InvalidContainerForCompile
     * @throws NotInstantiable
     */
    public function testInfiniteRecursion(): void
    {
        $this->expectException(\Cekta\DI\Exception\InfiniteRecursion::class);
        $this->expectExceptionMessage(
            sprintf(
                'Infinite recursion detected for `%s`, stack: %s, %s',
                A::class,
                A::class,
                B::class
            )
        );

        (new ContainerFactory())->make(
            filename: $this::FILE,
            fqcn: $this::FQCN,
            force_compile: true,
            containers: [
                A::class,
            ],
            params: [
                'username' => 'value for username',
                'dsn' => 'value for dsn',
            ]
        );
    }
}

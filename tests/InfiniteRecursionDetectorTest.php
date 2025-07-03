<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerFactory;
use Cekta\DI\Test\Fixture\Example\InfiniteRecursionExample;
use Cekta\DI\Test\Fixture\InfiniteRecursion;
use Cekta\DI\Test\Fixture\OtherUseS;
use PHPUnit\Framework\TestCase;

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

    public function testInfiniteRecursion(): void
    {
        $this->expectException(\Cekta\DI\Exception\InfiniteRecursion::class);
        $this->expectExceptionMessage(
            sprintf(
                'Infinite recursion detected for `%s`, stack: %s, %s',
                InfiniteRecursionExample::class,
                InfiniteRecursionExample::class,
                InfiniteRecursion::class
            )
        );

        (new ContainerFactory())->make(
            filename: $this::FILE,
            fqcn: $this::FQCN,
            force_compile: true,
            containers: [
                OtherUseS::class,
                InfiniteRecursionExample::class,
            ],
            params: [
                'username' => 'value for username',
                'dsn' => 'value for dsn',
            ]
        );
    }
}

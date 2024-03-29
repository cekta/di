<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Test\Fixture\ExampleRecursion;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class InfiniteRecursionTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test(): void
    {
        $this->expectException(InfiniteRecursion::class);
        $message = 'Infinite recursion for `Cekta\DI\Test\Fixture\ExampleRecursion`, ';
        $message .= 'calls: `Cekta\DI\Test\Fixture\ExampleRecursion`';
        $this->expectExceptionMessage($message);
        (new ContainerBuilder())->build()->get(ExampleRecursion::class);
    }
}

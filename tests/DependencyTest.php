<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Dependency;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    public function testCreate(): void
    {
        $name = 'test';
        $mock = $this->createMock(\ReflectionParameter::class);
        $obj = new Dependency(name: $name, parameter: $mock);
        Assert::assertSame($name, $obj->name);
        Assert::assertSame($mock, $obj->parameter);
    }
}

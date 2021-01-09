<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Reflection;

use Cekta\DI\Reflection\ParameterService;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use ReflectionType;

class ParameterServiceTest extends TestCase
{
    public function testGetName(): void
    {
        $this->expectException(LogicException::class);
        $service = new ParameterService();
        $mock = $this->createMock(ReflectionParameter::class);
        $mock->method('getType')
            ->willReturn($this->createMock(ReflectionType::class));
        $service->getName($mock);
    }
}

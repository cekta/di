<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;
use UnexpectedValueException;

class BuildFailTest extends TestCase
{
    public function test(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('`stdClass` must implement Psr\Container\ContainerInterface');
        (new ContainerBuilder())
            ->fqcn(stdClass::class)
            ->build();
    }
}

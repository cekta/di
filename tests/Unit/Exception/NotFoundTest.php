<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Exception;

use Cekta\DI\Exception\NotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    public function testMessage(): void
    {
        static::assertEquals('Container `name` not found', (new NotFound('name'))->getMessage());
    }
}

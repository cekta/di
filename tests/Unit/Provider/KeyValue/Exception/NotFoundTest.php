<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\KeyValue\Exception;

use Cekta\DI\Provider\KeyValue\Exception\NotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    public function testMessage()
    {
        $e = new NotFound('123');
        static::assertSame('Container `123` not found', $e->getMessage());
    }
}

<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider\Exception;

use Cekta\DI\Provider\Exception\NotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    public function testMessage()
    {
        $exception = new NotFound('id');
        $this->assertSame('Container `id` not found', $exception->getMessage());
    }
}

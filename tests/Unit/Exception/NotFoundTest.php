<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Exception;

use Cekta\DI\Exception\NotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    public function testMessage()
    {
        $exception = new NotFound('name');
        $this->assertEquals('Container `name` not found', $exception->getMessage());
    }
}

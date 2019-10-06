<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Exception;

use Cekta\DI\Exception\ProviderNotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    public function testMessage(): void
    {
        $this->assertEquals('Provider not found for `name`', (new ProviderNotFound('name'))->getMessage());
    }
}

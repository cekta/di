<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerExceptionInterface;
use Throwable;

interface ProviderExceptionInterface extends ContainerExceptionInterface, Throwable
{
}

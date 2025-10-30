<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use Cekta\DI\LoaderDTO;

class InvalidLoaderResult extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct(sprintf('callable must return instanceof %s', LoaderDTO::class));
    }
}

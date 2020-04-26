<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use InvalidArgumentException;

class FilePHP extends KeyValue
{
    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("invalid path: `$path`");
        }
        /** @noinspection PhpIncludeInspection */
        parent::__construct(require $path);
    }
}

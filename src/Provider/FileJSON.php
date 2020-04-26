<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use InvalidArgumentException;

class FileJSON extends KeyValue
{
    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("invalid path: `$path`");
        }
        $content = file_get_contents($path);
        assert(is_string($content));
        parent::__construct(json_decode($content, true));
    }
}

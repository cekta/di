<?php

declare(strict_types=1);

namespace Cekta\DI;

interface Disposable
{
    public function dispose(): void;
}

<?php
declare(strict_types=1);

namespace Cekta\DI;

interface ProviderInterface
{
    /**
     * @param string $id
     * @return mixed
     * @throws ProviderException
     */
    public function provide(string $id);

    public function canProvide(string $id): bool;
}

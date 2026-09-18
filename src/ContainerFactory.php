<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

/**
 * @external
 */
class ContainerFactory
{
    public function create(AbstractProject $project): ContainerInterface
    {
        $container = new $project->fqcn($project->params);
        if (!$container instanceof ContainerInterface) {
            throw new \RuntimeException(
                "`{$project->fqcn}` must implement " .
                    ContainerInterface::class,
            );
        }
        return $container;
    }
}

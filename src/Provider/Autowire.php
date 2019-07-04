<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowire\ReaderException;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Cekta\DI\Provider\Exception\NotReadable;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;

class Autowire implements ProviderInterface
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        try {
            $args = [];
            foreach ($this->reader->getDependencies($id) as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return new $id(...$args);
        } catch (ReaderException $exception) {
            throw new NotReadable($id, $exception);
        }
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }
}

<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowire\Exception\NotFound;
use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Cekta\DI\Provider\Autowire\RuleInterface;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;

class Autowire implements ProviderInterface
{
    /**
     * @var RuleInterface[]
     */
    private $rules;
    /**
     * @var ReaderInterface
     */
    private $reader;

    public function __construct(ReaderInterface $reader, RuleInterface ...$rules)
    {
        $this->reader = $reader;
        $this->rules = $rules;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        try {
            $args = [];
            foreach ($this->reader->getDependencies($id) as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return new $id(...$args);
        } catch (InvalidClassName $exception) {
            throw new NotFound($id, $exception);
        }
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }
}

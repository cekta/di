<?php
declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\NotFound;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function get($name)
    {
        if (!$this->has($name)) {
            throw new NotFound($name);
        }
        return $this->values[$name];
    }


    public function has($name)
    {
        return array_key_exists($name, $this->values);
    }
}
